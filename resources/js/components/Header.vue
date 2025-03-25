<script>
export default {
    name:"Header",
    props: ['title'],
    data() {},
    methods: {},
};
</script>
<template>
    <div class="bg-white flex items-center w-full text-lg font-semibold" id="header-ppal" >
        <div class="container mx-auto flex justify-between">
            <h1>{{ title }}</h1>
            <span class="user ">Johan Páez</span>
        </div>
    </div>
</template>
