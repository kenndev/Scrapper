<template>
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <li
                class="page-item"
                :class="{ disabled: pagination.current_page === 1 }"
            >
                <a
                    class="page-link"
                    href="#"
                    @click.prevent="changePage(pagination.first_page)"
                    >First</a
                >
            </li>
            <li
                class="page-item"
                :class="{ disabled: pagination.current_page === 1 }"
            >
                <a
                    class="page-link"
                    href="#"
                    @click.prevent="changePage(pagination.current_page - 1)"
                    >Previous</a
                >
            </li>
            <li
                class="page-item"
                v-for="pageNumber in pages"
                :key="pageNumber"
                :class="{ active: pageNumber == pagination.current_page }"
            >
                <a
                    class="page-link"
                    href="#"
                    @click.prevent="changePage(pageNumber)"
                    >{{ pageNumber }}</a
                >
            </li>
            <li
                class="page-item"
                :class="{
                    disabled: pagination.current_page === pagination.last_page,
                }"
            >
                <a
                    class="page-link"
                    href="#"
                    @click.prevent="changePage(pagination.current_page + 1)"
                    >Next</a
                >
            </li>
            <li
                class="page-item"
                :class="{
                    disabled: pagination.current_page == pagination.last_page,
                }"
            >
                <a
                    class="page-link"
                    href="#"
                    @click.prevent="changePage(pagination.last_page)"
                    >Last</a
                >
            </li>
            <span class="m-1"> showing {{ totalItems }} of {{ pagination.total }} entries.</span>
        </ul>
    </nav>
</template>
<script>
import { ref, onMounted } from "vue";
export default {
    name: "Paginator",
    props: {
        pagination: {
            type: Object,
            required: true,
        },
        totalItems: {
            type: Number,
            required: true,
        },
    },
    setup(props, { emit }) {
        const offset = 4;

        const pages = ref([]);

        const { last_page, current_page, from, to } = props.pagination;

        const pagesNumbers = () => {
            if (!to) return [];

            let fromPage = current_page - offset;

            if (fromPage < 1) fromPage = 1;

            let toPage = fromPage + offset * 2;

            if (toPage >= last_page) {
                toPage = last_page;
            }

            for (let page = fromPage; page <= toPage; page++) {
                pages.value.push(page);
            }

            return pages;
        };

        const changePage = (pageNumber) => {
            emit("pageChanged", pageNumber);
        };

        onMounted(() => {
            pagesNumbers();
        });

        return {
            pages,
            pagesNumbers,
            changePage,
        };
    },
};
</script>
