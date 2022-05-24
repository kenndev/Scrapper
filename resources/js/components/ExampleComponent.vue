<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6 col-lg-3 col-sm-12">
                                <Datepicker
                                    v-model="date"
                                    :transitions="true"
                                    :format="format"
                                ></Datepicker>
                            </div>
                            <div class="col-md-6 col-lg-9 col-sm-12 mt-2">
                                <div class="excel float-sm-left mb-2">
                                    <button
                                        class="btn-block btn btn-sm btn-primary float-sm-left"
                                        @click="download"
                                    >
                                        Download excel
                                    </button>
                                </div>
                                <div class="excel float-sm-left mb-2 ms-1">
                                    <button
                                        class="btn-block btn btn-sm btn-primary float-sm-left"
                                        @click="deleteSelected"
                                    >
                                        Delete Selected
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <datatable-component
                            :url="url"
                            :columns="[
                                'select',
                                'title',
                                'description',
                                'created_at',
                                'actions',
                            ]"
                            :actions="actions"
                            :key="comics_reload"
                            ref="exposedFunc"
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
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    name: "Example",
    components: {
        DatatableComponent,
        Datepicker,
    },
    setup() {
        const exposedFunc = ref();

        let loader = useLoading();
        const date = ref(new Date());
        const url = "/api/get-articles/";
        const comics_reload = ref(0);
        const actions = [
            // {
            //     id: 1,
            //     url: "details",
            //     name: "Details",
            //     type: "link",
            // },
            // {
            //     id: 2,
            //     url: "edit",
            //     name: "Edit",
            //     type: "link",
            // },
            {
                id: 3,
                url: "delete",
                name: "Delete",
                type: "button",
            },
        ];
       
       
        const deleteSelected = () => {
            exposedFunc.value.getSelectedArticles()
        };

        const download = async () => {
            //console.log(getCompanyName.value.getCompanyName());
            let newDate = format(date.value);
            //console.log(newDate);

            loader.show({
                loader: "dots",
                color: "#000000",
                backgroundColor: "#050505",
            });

            const url =
                "/api/exportarticles/" +
                exposedFunc.value.getCompanyName() +
                "/" +
                newDate;
            window.location.href = url;
            loader.hide();
            comics_reload.value++;
        };

        const format = (date) => {
            const day = date.getDate();
            const month = date.getMonth() + 1;
            const year = date.getFullYear();

            return `${day}-${month}-${year}`;
        };

        return {
            url,
            actions,
            comics_reload,
            exposedFunc,
            date,
            format,
            download,
            deleteSelected,
        };
    },
};
</script>
