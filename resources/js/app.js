/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");


import { createApp } from "vue";
import router from "./router";
import NavComponent from "./components/NavComponent";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import CKEditor from '@ckeditor/ckeditor5-vue';

const options = {
    // You can set your default options here
};

createApp({
    components: {
        'nav-component': NavComponent,
    },
}).use(router).use(Toast, options).use( CKEditor ).mount("#app");
