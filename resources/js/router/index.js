import { createRouter, createWebHistory } from "vue-router";
import Example from "../components/ExampleComponent";
import ArticleDetails from "../components/ArticleDetails";

const routes = [
    {
        path: "/home",
        name: "home",
        component: Example,
    },
    {
        path: "/article/:id/details",
        name: "details",
        component: ArticleDetails,
        props: true,
    },
];

export default createRouter({
    history: createWebHistory(),
    mode: "history",
    routes: [],
    routes,
});
