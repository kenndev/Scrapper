<template>
    <router-link
        class="navbar-brand headColor"
        :to="{
            name: 'home',
        }"
    >
        Home
    </router-link>
</template>

<script>
export default {
    name: "Nav",
};
</script>

<style>
.headColor {
    color: rgba(236, 242, 244, 1) !important;
}
</style>
