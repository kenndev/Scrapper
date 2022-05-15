import { createRouter, createWebHistory } from "vue-router";
import Example from "../components/ExampleComponent";
import ArticleDetails from "../components/ArticleDetails";
import ArticleEdit from "../components/EditComponent"

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
    {
        path: "/article/:id/edit",
        name: "edit",
        component: ArticleEdit,
        props: true,
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: "/home",
    }
];

export default createRouter({
    history: createWebHistory(),
    mode: "history",
    routes: [],
    routes,
});
