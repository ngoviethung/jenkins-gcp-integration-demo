<template>
    <div class="box col-md-12 padding-10 p-t-20">
        <div class="form-group col-xs-12 ng-scope">
            <label>Topics</label>
            <div class="array-container form-group">
                <multiselect v-model="selectedTopics" :options="topics"
                             :multiple="true"
                             :preserve-search="true"
                             label="name"
                             track-by="name"
                />
                <div class="array-container form-group">
                    <table class="table table-bordered table-striped m-b-0">
                        <thead>
                        <tr>
                            <th style="font-weight: 600 !important;">Name</th>
                            <th style="font-weight: 600 !important;">Types</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="array-row ng-scope" v-for="(topic,index) in selectedTopics">
                            <td>
                                <input type="text"
                                       class="form-control input-sm ng-pristine ng-untouched ng-valid ng-empty"
                                       readonly :value="topic.name">

                            </td>
                            <td>
                                <multiselect v-model="selectedTopics[index].types" :options="types"
                                             :multiple="true"
                                             :preserve-search="true"
                                             :preselect-first="true"
                                             label="name"
                                             track-by="name"
                                />
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <template v-for="(topic,index) in selectedTopics">
                        <input type="text" :name="'topics['+index+'][topic_id]'" :value="topic.id" hidden>
                        <input
                            v-for="(type,index2) in selectedTopics[index].types"
                            type="text" :name="'topics['+index+'][type_ids][]'" :value="type.id" hidden>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import 'vue-multiselect/dist/vue-multiselect.min.css';
    import axios from 'axios';
    import Multiselect from 'vue-multiselect'

    export default {
        components: {Multiselect},
        name: "StyleBuilder",
        props: ['id'],
        data() {
            return {
                topics: [],
                selectedTopics: [],
                types: [],

            }
        },
        methods: {
            async fetchTopics() {
                try {
                    const resource = '/api-admin/topics';
                    let {data} = await axios.get(resource);
                    this.topics = data.data;
                } catch (error) {
                    alert("Lấy danh sách Topic thất bại vui lòng F5 lại");
                }
            },
            async fetchTypes() {
                try {
                    const resource = '/api-admin/types';
                    let {data} = await axios.get(resource);
                    this.types = data.data;
                } catch (error) {
                    alert("Lấy danh sách Type thất bại vui lòng F5 lại");
                }
            },
            async fetchCurrentTopics() {
                try {
                    const resource = `/api-admin/items/${this.id}/topics`;
                    let {data} = await axios.get(resource);
                    this.selectedTopics = data.data;
                } catch (error) {
                    alert("Lấy danh sách Topic hiện tại thất bại vui lòng F5 lại");
                }
            }
        },
        //Life Cycle
        created() {
            this.fetchTopics();
            this.fetchTypes();
            if (this.id) {
                this.fetchCurrentTopics();
            }
        }
    }
</script>

<style scoped>

</style>
