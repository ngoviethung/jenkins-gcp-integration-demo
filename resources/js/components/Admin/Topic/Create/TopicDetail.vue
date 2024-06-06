<template>
    <div class="box col-md-12 padding-10 p-t-20">
        <div class="form-group col-xs-12 ng-scope">
            <label>Type</label>
            <multiselect v-model="typeSelected"
                         :options="types"
                         :allow-empty="false"
                         :preselect-first="true"
                         placeholder="Select one"
                         label="name" track-by="name"
            />
            <label>Items</label>
            <multiselect v-model="itemSelecteds" :options="items"
                         :multiple="true"
                         :preserve-search="true"
                         :preselect-first="true"
                         :allow-empty="false"
                         label="name"
                         track-by="name"
            />
            <input type="text"
                   :name="'topics['+stt+'][type_id]'"
                   :value="typeIdSelected"
                   hidden/>
            <input type="text"
                   v-for="item in itemSelecteds"
                   :name="'topics['+stt+'][item_ids][]'"
                   :value="item.id" hidden/>
            <button class="btn btn-danger button-delete" @click.prevent="deleteClicked">X</button>
        </div>
    </div>
</template>

<script>
    import 'vue-multiselect/dist/vue-multiselect.min.css';
    import Multiselect from 'vue-multiselect'

    export default {
        name: "TopicDetail",
        props: ['stt', 'index', 'items', 'types'],
        components: {Multiselect},
        data() {
            return {
                itemSelecteds: [],
                typeSelected: null,
            }
        },
        computed: {
            typeIdSelected() {
                return (this.typeSelected) ? this.typeSelected.id : null;
            }
        },
        methods: {
            deleteClicked() {
                this.$emit('delete', this.index);
            }
        }
    }
</script>

<style scoped>
    .button-delete {
        margin-top: 4%;
    }
</style>
