<template>
    <div>
        <div class="row mb-3 actions">
            <div class="col-md-8 col-sm-12 selecs">
                <div class="col-md-4 input-group selecs-item">
                    <label for="pageOption" class="mt-2 mr-2">Per page</label>
                    <select
                        class="form-select"
                        v-model="perPage"
                        @change="handlePerPage"
                        id="pageOption"
                    >
                        <option
                            v-for="page in pageOptions"
                            :key="page"
                            :value="page"
                        >
                            {{ page }}
                        </option>
                    </select>
                </div>
                <div class="col-md-4 input-group selecs-item">
                    <label for="pageOption" class="mt-2 mr-2">Company</label>
                    <select
                        class="form-select"
                        v-model="companyName"
                        @change="handleCompany"
                        id="companyOption"
                    >
                        <option value="0">All</option>
                        <option
                            v-for="company in companies"
                            :key="company"
                            :value="company.id"
                        >
                            {{ company.name }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="input-group">
                    <input
                        type="text"
                        v-model="search"
                        class="form-control"
                        placeholder="Search..."
                    />
                    <div class="input-group-append">
                        <button
                            class="btn btn-dark"
                            type="button"
                            @click.prevent="handleSearch"
                        >
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover" id="dataTable">
                <thead>
                    <tr>
                        <th
                            scope="col"
                            v-for="column in columns"
                            :key="column"
                            @click="updateSortColumn(column)"
                        >
                            {{
                                column
                                    .replace(/(^\w{1})|(\s+\w{1})/g, (letter) =>
                                        letter.toUpperCase()
                                    )
                                    .replace("_", " ")
                            }}
                            <span v-if="column === sortField">
                                <i
                                    v-if="sortOrder === 'asc'"
                                    class="fa fa-arrow-up fa-1x"
                                ></i>
                                <i v-else class="fa fa-arrow-down fa-1x"></i>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="tableData.length === 0">
                        <div class="m-3">No Data Found</div>
                    </tr>
                    <tr
                        v-else
                        v-for="data in tableData"
                        :key="data"
                        :class="{
                            highlight: data['edited'] == 1,
                        }"
                    >
                        <td
                            :class="column === 'description' ? 'col-6' : ''"
                            v-for="column in columns"
                            :key="column"
                        >
                            <div v-if="column === 'select'">
                                <label class="form-checkbox">
                                    <input
                                        type="checkbox"
                                        :value="data.id"
                                        v-model="selected"
                                    />
                                    <i class="form-icon"></i>
                                </label>
                            </div>
                            <div v-if="column === 'actions'">
                                <actions
                                    :model_id="data.id"
                                    :actions="actions"
                                    @update-table="tableUpdated"
                                />
                            </div>
                            <div
                                v-if="column == 'created_at'"
                                @click="rowClicked(data.id)"
                            >
                                {{ moment(data[column]).format("DD-MM-YYYY") }}
                            </div>
                            <div
                                @click="rowClicked(data.id)"
                                v-else
                                v-html="
                                    column == 'description'
                                        ? data[column].slice(0, 400) + '...'
                                        : data[column]
                                "
                            ></div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <paginator
                v-if="tableData.length > 0"
                :pagination="pagination"
                :totalItems="tableData.length"
                @pageChanged="handlePageChange"
                :key="pagination_reload"
            />
        </div>
    </div>
</template>
<script>
import { ref, onMounted, reactive } from "vue";
import axios from "axios";
import Paginator from "./PaginatorComponent.vue";
import Actions from "./ActionsComponent.vue";
import moment from "moment";
import router from "../router";
import { useLoading } from "vue3-loading-overlay";
import "vue3-loading-overlay/dist/vue3-loading-overlay.css";
import { useToast } from "vue-toastification";
export default {
    name: "Datatable",
    props: {
        url: {
            type: String,
            required: true,
        },
        columns: {
            type: Array,
            required: true,
        },
        actions: {
            type: Array,
            required: true,
        },
    },
    components: {
        Paginator,
        Actions,
    },
    setup(props, { expose }) {
        let loader = useLoading();

        const pagination_reload = ref(0);
        const selected = ref([]);

        const deleteArticlesId = reactive({
            ids: selected,
        });

        const companies = ref([]);
        const tableData = ref([]);
        const sortField = ref();
        const sortOrder = ref("desc");
        const search = ref("");
        const pageOptions = [5, 10, 20, 50];
        const perPage = ref(10);
        const pagination = ref({ to: 1, from: 1 });
        const page = ref(1);
        const actions = props.actions;
        const companyName = ref(0);

        const formatDate = (date) => {
            return moment().format(date);
        };

        const fetchCompanies = async () => {
            let res = await axios.get("/api/companies");
            companies.value = res.data.data;
        };

        const fetchData = async () => {
            const params = {
                sort_field: sortField.value,
                sort_order: sortOrder.value,
                per_page: perPage.value,
                search: search.value,
                page: page.value,
                company_name: companyName.value,
            };
            loader.show({
                loader: "dots",
                color: "#000000",
                backgroundColor: "#050505",
            });

            let response = await axios.get(props.url, { params });
            tableData.value = response.data.data;
            pagination.value = response.data.meta;
            //reload pagination component of parent reload
            loader.hide();
            pagination_reload.value++;
        };

        const updateSortColumn = (column) => {
            if (column === sortField.value) {
                sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
            } else {
                sortField.value = column;
                sortOrder.value = "desc";
            }
            fetchData();
        };

        const rowClicked = (articleid) => {
            router.push({ name: "edit", params: { id: articleid } });
        };

        const handleSearch = () => {
            page.value = 1;
            sortOrder.value = "desc";
            sortField.value = props.columns[2];
            fetchData();
        };

        const handleCompany = ($event) => {
            companyName.value = $event.target.value;
            page.value = 1;
            fetchData();
        };

        const handlePerPage = ($event) => {
            perPage.value = $event.target.value;
            page.value = 1;
            fetchData();
        };

        const handlePageChange = (pageNumber) => {
            page.value = pageNumber;
            fetchData();
        };

        const tableUpdated = (childData) => {
            console.log(childData);
            fetchData();
        };

        onMounted(() => {
            fetchCompanies();
            fetchData();
        });

        const getCompanyName = () => {
            return companyName.value;
        };

        const getSelectedArticles = async () => {
            try {
                const toast = useToast();
                let res = await axios.post("api/deletebulk", {
                    ...deleteArticlesId,
                });
                fetchData();
                toast.success(res.data.data.message, {
                    timeout: 5000,
                });

            } catch (error) {
                console.log(error);
            }

            // selected.value.forEach((element) => {
            //     console.log(element);
            // });
        };

        expose({ getCompanyName, getSelectedArticles });

        return {
            tableData,
            sortField,
            sortOrder,
            search,
            pageOptions,
            perPage,
            pagination,
            handlePerPage,
            handlePageChange,
            updateSortColumn,
            handleSearch,
            actions,
            formatDate,
            moment,
            tableUpdated,
            fetchCompanies,
            handleCompany,
            companies,
            pagination_reload,
            getCompanyName,
            companyName,
            rowClicked,
            selected,
            getSelectedArticles,
            deleteArticlesId,
        };
    },
};
</script>
<style scoped>
.highlight {
    /* background-color: rgba(0, 128, 128, 0.6); */
    background-color: rgb(63, 224, 208);
}
.nothighlighted {
    background-color: rgba(241, 4, 16, 0.3);
}
.nothighlightedhack {
    background-color: rgba(241, 4, 16, 0.3);
}
tr:hover {
    cursor: pointer;
}
.actions {
    display: flex;
    justify-content: space-between;
}

.selecs {
    display: flex;
}
</style>
