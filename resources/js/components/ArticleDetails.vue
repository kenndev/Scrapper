<template>
    <div>
        <div class="card">
            <div class="card-body">
                <h3 class="text-center">
                    {{ article.title }}
                </h3>

                <p v-html="article.description"></p>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted, ref } from "vue";
import { useLoading } from "vue3-loading-overlay";
import "vue3-loading-overlay/dist/vue3-loading-overlay.css";
import axios from "axios";
export default {
    name: "ArticleDetails",
    props: {
        id: {
            required: true,
            type: String,
        },
    },

    setup(props) {
        let loader = useLoading();
        const article = ref([]);
        
        const getArticleDetails = async () => {
            loader.show({
                loader: "dots",
                color: "#000000",
                backgroundColor: "#050505",
            });
            const response = await axios.get(
                "/api/article-details/" + props.id
            );
            article.value = response.data.data;
            loader.hide();
        };

        onMounted(() => {
            getArticleDetails();
        });

        return {
            article,
            getArticleDetails,
        };
    },
};
</script>

<style>

</style>
