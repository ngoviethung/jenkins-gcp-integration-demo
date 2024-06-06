<template>
    <div class="box col-md-12 padding-10 p-t-20">
        <div class="form-group col-xs-12 ng-scope">
            <label>Types</label>
            <multiselect v-model="selecteds" :options="options"
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
                        <th style="font-weight: 600 !important;">Factor</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="array-row ng-scope" v-for="(type,index) in selecteds">
                        <td>
                            <input type="text" class="form-control input-sm ng-pristine ng-untouched ng-valid ng-empty"
                                   readonly :value="type.name">

                        </td>
                        <td>
                            <input type="text" :name="'types['+index+'][id]'" :value="type.id"
                                   class="form-control id-input hidden">
                            <input step="0.001" type="number" :name="'types['+index+'][factor]'"
                                   v-model="selecteds[index].factor"
                                   required
                                   class="form-control input-sm ng-pristine ng-untouched ng-valid ng-empty">
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</template>
<script>
    import 'vue-multiselect/dist/vue-multiselect.min.css';
    import Multiselect from 'vue-multiselect';
    import axios from 'axios';

    const resource = '/api-admin/types';
    export default {
        components: {Multiselect},
        name: "TypeBuilder",
        props: ['id'],
        data() {
            return {
                selecteds: [],
                options: []
            }
        },
        created() {
            this.fetch();
            // only call in edit form to get current list types of item
            if (this.id) {
                this.fetchCurrentTypes();
            }
        },
        methods: {
            async fetch() {
                try {
                    let {data} = await axios.get(resource);
                    const types = data.data
                    let typesObject = data.data.map(element => {
                        return {id: element.id, name: element.name, factor: null}
                    });
                    this.options = typesObject;
                } catch (error) {
                    alert("Lấy danh sách Type thất bại vui lòng F5 lại");
                }
            },
            // this methods for edit form
            async fetchCurrentTypes() {
                const resourceCurrentTypes = `/api-admin/tasks/${this.id}/types`;
                try {
                    const {data} = await axios.get(resourceCurrentTypes);
                    this.selecteds = data.data;
                } catch (error) {
                    alert("Lấy danh sách Type hiện tại thất bại vui lòng F5 lại");
                }
            }
        }
    }
</script>

<style scoped>
    .id-input {
        display: none;
    }
</style>
