<template>
    <div>
        <div class="card">
            <div class="card-body">
                <form name="myForm" class="" @submit.prevent="updateArticle">
                    <div class="row mb-3">
                        <div class="">
                            <label for="TopIc" class="col-sm-3 col-form-label">Title<span
                                    class="must-fill">*</span></label>
                            <textarea id="Title" type="text" class="form-control" name="Title" v-model="form.title"
                                rows="3" required aria-label="Title" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="">
                            <label for="exampleFormControlTextarea1" class="col-sm-3 col-form-label">Description</label>
                            <ckeditor :editor="ClassicEditor" v-model="form.description" :config="editorConfig">
                            </ckeditor>
                        </div>
                    </div>
                    <div class="col-md-12 form-btn d-flex flex-row justify-content-center">
                        <button type="sumbit" class="btn btn-md btn-primary">
                            Update Article
                        </button>
                        <button type="button" @click="deleteArticle(form.id)" class="ms-1 btn btn-md btn-warning">
                            Delete Article
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script setup>
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import axios from "axios";
import { useToast } from "vue-toastification";
import { useLoading } from "vue3-loading-overlay";
import { reactive, onMounted, ref } from "vue";
import router from '../router';
import { isIntegerKey } from "@vue/shared";

let loader = useLoading();


const editorConfig = reactive({});
const errors = ref([]);
let props = defineProps({
    id: {
        required: true,
        type: String,
    },
    company: {
        type: String,
        default: 0,
    },
    page: {
        type: String,
        default: 1,
    }
});

const form = reactive({
    title: "",
    description: "",
    id: props.id,
    company: "",
});


const fetchArticle = async () => {
    const response = await axios.get("/api/article-details/" + props.id);
    form.description = response.data.data.description;
    form.title = response.data.data.title;
    form.company = response.data.data.url_id;
};

const updateArticle = async () => {
    loader.show({
        loader: "dots",
        color: "#000000",
        backgroundColor: "#050505",
    });
    const toast = useToast();
    errors.value = "";
    try {
        const res = await axios.post("/api/update-article", form);
        toast.success(res.data.data.message, {
            timeout: 5000,
        });
        loader.hide();
        if (props.company == 0) {
            router.push({
                name: "home", query: {
                    page: props.page,
                },
            });

        } else {
            router.push({
                name: "home", query: {
                    company: form.company,
                    page: props.page,
                },
            });
        }
        // router.go(-1)
    } catch (error) {
        if (error.res.status === 422) {
            errors.value = error.res.data.errors;
        }
    }
};

const deleteArticle = async (article_id) => {
    const toast = useToast();
    if (confirm('Are you sure you want to delete this record?')) {
        const res = await axios.get("/api/delete/" + article_id);
        if (props.company == 0) {
            router.push({
                name: "home", query: {
                    page: props.page,
                },
            });
        } else {
            router.push({
                name: "home", query: {
                    company: form.company,
                    page: props.page,
                },
            });
        }
        toast.success(res.data.data.message, {
            timeout: 5000,
        });
    }
};

onMounted(() => {

    console.log(props.company + " " + props.page)
    fetchArticle();
});
</script>
