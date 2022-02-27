<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="excel float-sm-left mb-2">
                            <button
                                class="btn btn-sm btn-primary float-sm-left"
                                @click="download"
                            >
                                Download excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <datatable-component
                            :url="url"
                            :columns="[
                                'title',
                                'description',
                                'created_at',
                                'actions',
                            ]"
                            :actions="actions"
                            :key="comics_reload"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import DatatableComponent from "./DatatableComponent.vue";
import { useLoading } from "vue3-loading-overlay";
import "vue3-loading-overlay/dist/vue3-loading-overlay.css";
import { ref } from "vue";
export default {
    name: "Example",
    components: {
        DatatableComponent,
    },
    setup() {
        let loader = useLoading();

        const url = "/api/get-articles/";
        const comics_reload = ref(0);
        const actions = [
            {
                id: 1,
                url: "details",
                name: "Details",
                type: "link",
            },
            {
                id: 2,
                url: "status",
                name: "Change Status",
                type: "button",
            },
        ];

        const download = async () => {
            loader.show({
                loader: "dots",
                color: "#000000",
                backgroundColor: "#050505",
            });
            const url = "/api/exportarticles";
            window.location.href = url;
            loader.hide();
            comics_reload.value++;
        };

        return {
            url,
            actions,
            comics_reload,
            download,
        };
    },
};
</script>
