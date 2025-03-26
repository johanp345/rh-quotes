<?php

namespace RH\Quotes\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * Clase QuoteService
 * Proporciona funcionalidades para interactuar con una API de citas (quotes), incluyendo almacenamiento en caché,
 * paginación, búsqueda binaria y manejo de límites de tasa (rate limiting).
 */
class QuoteService
{
    protected $config;
    protected $httpClient;
    protected $cachedQuotes;
    protected $cacheKey = 'quotes_cache';
    protected $cacheTtl = 3600; // 1 hora en segundos
    protected $totalPages = 1;

    /**
     * @var array $config Configuración del servicio, incluyendo la URL base de la API y límites de tasa.
     * @var \Illuminate\Http\Client\PendingRequest $httpClient Cliente HTTP configurado con la URL base.
     * @var \Illuminate\Support\Collection $cachedQuotes Colección de citas almacenadas en caché.
     * @var string $cacheKey Clave utilizada para almacenar las citas en caché.
     * @var int $cacheTtl Tiempo de vida de la caché en segundos (1 hora por defecto).
     */

    /**
     * Constructor de la clase.
     *
     * @param array $config Configuración del servicio (base_url, rate_limit, rate_window, etc.).
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->httpClient = Http::baseUrl($config['base_url']);
        $this->initializeCache();
    }

    /**
     * Inicializa la caché de citas. Si no existe, crea una colección vacía.
     */
    private function initializeCache()
    {
        $this->cachedQuotes = Cache::remember($this->cacheKey, $this->cacheTtl, function () {
            return collect();
        });
        $this->totalPages = Cache::remember("total_pages_quotes", $this->cacheTtl, function () {
            return 1;
        });
    }

    /**
     * Obtiene todas las citas con paginación.
     *
     * @param int $page Número de página (por defecto 1).
     * @param int $perPage Número de elementos por página (por defecto 10).
     * @return array Citas paginadas y datos de paginación.
     */
    public function getAllQuotes(int $page = 1, int $perPage = 30)
    {
        $this->handleRateLimit();

        // Verifica si la página solicitada está en caché
        if ($this->cachedQuotes->isEmpty() || $this->cachedQuotes->count() < $page * $perPage) {
            $this->fetchAndCachePage($page, $perPage);
        }

        return $this->paginate($page, $perPage);
    }

    /**
     * Verifica si la página solicitada está en caché.
     */
    private function isPageCached(int $page, int $perPage): bool
    {
        $start = ($page - 1) * $perPage;
        $end = $start + $perPage - 1;

        // Verifica si todos los elementos de la página están en caché
        for ($i = $start; $i <= $end; $i++) {
            if (!isset($this->cachedQuotes[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtiene todas las citas desde la API y las almacena en caché.
     * Realiza múltiples solicitudes para obtener todas las páginas de resultados.
     */
    private function fetchAndCachePage(int $page, int $perPage)
    {
        $response = $this->httpClient->get('/quotes', [
            'limit' => $perPage,
            'skip' => ($page - 1) * $perPage
        ]);

        $data = $response->json();
        $quotes = $data['quotes'];
        $this->totalPages = $data['total'];

        $this->handleRateLimit();

        // Inserta las citas de la página en la caché
        $mergeQuotes = array_merge($quotes, $this->cachedQuotes->toArray()); // Convertir la colección a array

        // Ordenar la caché por ID para búsqueda binaria
        $this->cachedQuotes = collect($mergeQuotes)->sortBy('id')->values(); // Convertir de nuevo a colección

        Cache::put($this->cacheKey, $this->cachedQuotes, $this->cacheTtl);
        Cache::put("total_pages_quotes", $this->totalPages, $this->cacheTtl);
    }

    /**
     * Pagina las citas almacenadas en caché.
     *
     * @param int $page Número de página.
     * @param int $perPage Número de elementos por página.
     * @return array Citas paginadas y datos de paginación.
     */
    private function paginate(int $page, int $perPage)
    {
        $total = $this->totalPages;
        $paginated = $this->cachedQuotes
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        return [
            'quotes' => $paginated,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Realiza una búsqueda binaria en las citas almacenadas en caché para encontrar una cita por su ID.
     *
     * @param int $id ID de la cita a buscar.
     * @return int Índice de la cita en la colección o -1 si no se encuentra.
     */
    private function binarySearch(int $id)
    {
        $low = 0;
        $high = $this->cachedQuotes->count() - 1;

        while ($low <= $high) {
            $mid = (int)(($low + $high) / 2);
            $currentId = $this->cachedQuotes[$mid]['id'];

            if ($currentId === $id) {
                return $mid;
            }

            $currentId < $id ? $low = $mid + 1 : $high = $mid - 1;
        }

        return -1;
    }

    /**
     * Obtiene una cita específica por su ID.
     * Si no está en caché, intenta obtenerla desde la API y la agrega a la caché.
     *
     * @param int $id ID de la cita.
     * @return array|null Cita encontrada o null si no existe.
     */
    public function getQuote(int $id)
    {
        $index = $this->binarySearch($id);
        if ($index !== -1) {
            return $this->cachedQuotes[$index];
        }
        $this->handleRateLimit();
        if ($this->cachedQuotes->isEmpty()) {
            $response = $this->httpClient->get("/quotes/{$id}");
            if ($response->successful()) {
                $quote = $response->json();
                $this->addToCache($quote);
                return $quote;
            }
        }

        return null;
    }

    /**
     * Obtiene una cita aleatoria desde la API y la agrega a la caché.
     *
     * @return array|null Cita aleatoria o null si la solicitud falla.
     */
    public function getRandomQuote()
    {
        $this->handleRateLimit();
        $response = $this->httpClient->get('/quotes/random');
        if ($response->successful()) {
            $quote = $response->json();
            $this->addToCache($quote);
            return $quote;
        }
        return null;
    }

    /**
     * Agrega una cita a la caché y la ordena por ID.
     *
     * @param array $quote Cita a agregar.
     */ private function addToCache(array $quote)
    {
        $this->cachedQuotes->push($quote);
        $this->cachedQuotes = $this->cachedQuotes->sortBy('id')->values();
        Cache::put($this->cacheKey, $this->cachedQuotes, $this->cacheTtl);
    }

    /**
     * Maneja el límite de tasa (rate limit) para las solicitudes a la API.
     * Si se excede el límite, espera hasta que se pueda realizar una nueva solicitud.
     */
    private function handleRateLimit()
    {
        $key = 'quotes_rate_limit';
        $window = now()->timestamp / $this->config['rate_window'];
        $data = Cache::get($key, ['count' => 0, 'window' => floor($window)]);

        if ($data['window'] != floor($window)) {
            $data = ['count' => 0, 'window' => floor($window)];
        }

        if (++$data['count'] > $this->config['rate_limit']) {
            $remaining = ceil(($data['window'] + 1) * $this->config['rate_window'] - now()->timestamp);
            sleep($remaining);
            $data = ['count' => 1, 'window' => floor(now()->timestamp / $this->config['rate_window'])];
        }

        Cache::put($key, $data, $this->config['rate_window']);
    }
}
