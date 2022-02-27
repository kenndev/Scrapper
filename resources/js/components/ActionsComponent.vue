<template>
    <div class="dropdown show">
        <a
            class="btn btn-secondary dropdown-toggle"
            href="#"
            role="button"
            id="dropdownMenuLink"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
        >
            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
        </a>

        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
            <div v-for="action in actions" :key="action.id">
                <router-link
                    v-if="action.type === 'link'"
                    class="dropdown-item"
                    :to="{
                        name: action.url,
                        params: { id: model_id },
                    }"
                >
                    {{ action.name }}
                </router-link>
                <button
                    v-if="action.type === 'button'"
                    @click="performAction(model_id)"
                    class="dropdown-item"
                >
                    {{ action.name }}
                </button>
            </div>
        </div>
    </div>
</template>
<script>
import axios from "axios";
import { useToast } from "vue-toastification";
export default {
    name: "Actions",
    props: {
        model_id: {
            type: Number,
            required: true,
        },
        actions: {
            type: Array,
            required: true,
        },
    },
    setup(props, context) {
        const toast = useToast();
        const performAction = async (model_id) => {
            const res = await axios.get("/api/status/" + model_id);
            context.emit('update-table','table changed');
            toast.success(res.data.data[0].message, {
                timeout: 5000,
            });
        };
        return {
            performAction,
        };
    },
};
</script>
