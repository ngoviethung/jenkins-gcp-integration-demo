<template>
    <div class="box col-md-12 padding-10 p-t-20">
        <div class="form-group col-xs-12 ng-scope">
            <label>Styles</label>
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
                        <th style="font-weight: 600 !important;">Score</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="array-row ng-scope" v-for="(style,index) in selecteds">
                        <td>
                            <input type="text" class="form-control input-sm ng-pristine ng-untouched ng-valid ng-empty"
                                   readonly :value="style.name">

                        </td>
                        <td>
                            <input type="text" :name="'styles['+index+'][id]'" :value="style.id"
                                   class="form-control id-input hidden">
                            <input step="0.001" type="number" :name="'styles['+index+'][score]'"
                                   v-model="selecteds[index].score"
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
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<script>
    import Multiselect from 'vue-multiselect'
    import axios from 'axios';

    const resource = '/api-admin/styles';
    export default {
        components: {Multiselect},
        name: "StyleBuilder",
        props: ['id'],
        data() {
            return {
                selecteds: [],
                options: []
            }
        },
        created() {
            this.fetch();
            // only call in edit form to get current list styles of item
            if (this.id) {
                this.fetchCurrentStyles();
            }
        },
        methods: {
            async fetch() {
                try {
                    let {data} = await axios.get(resource);
                    data = data.map(element => {
                        return {id: element.id, name: element.name, score: null}
                    });
                    this.options = data;
                } catch (error) {
                    alert("Lấy danh sách Style thất bại vui lòng F5 lại");
                }
            },
            // this methods for edit form
            async fetchCurrentStyles() {
                const resourceCurrentStyles = `/api-admin/items/${this.id}/styles`;
                try {
                    const {data} = await axios.get(resourceCurrentStyles);
                    this.selecteds = data.data;
                } catch (error) {
                    alert("Lấy danh sách Style hiện tại thất bại vui lòng F5 lại");
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
