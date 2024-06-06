<template>
    <div class="box col-md-12 padding-10 p-t-20">
        <div class="form-group col-xs-12 mb-0">
            <div class="float-right">
                <a class="btn btn-xs btn-default select_all" style="margin-top: 5px;" v-on:click="selectAll">
                    <i class="fa fa-check-square-o"></i>Select All
                </a>
                <a class="btn btn-xs btn-default clear" style="margin-top: 5px;" v-on:click="clearAll">
                    <i class="fa fa-times"></i> Clear
                </a>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="form-group col-xs-12 ng-scope">
            <label >Types</label>
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
            selectAll: function (event) {
                console.log('SelectedIds', this.selecteds);
                console.log('Options',this.options);

                let result = [];
                for(let i = 0; i < this.options.length; i++) {
                    let found = false;
                    for(let j = 0; j < this.selecteds.length; j++) {
                        if(this.selecteds[j].id === this.options[i].id) {
                            result.push(this.selecteds[i]);
                            found = true;
                            break;
                        }
                    }

                    if(!found) {
                        result.push(this.options[i]);
                    }
                }

                this.selecteds = result;
            },
            clearAll: function (event) {
                this.selecteds = [];
            },
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
                const resourceCurrentTypes = `/api-admin/topics/${this.id}/types`;
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
