<template>
    <div id="root">
        <TopicDetail
            v-for="(topic,stt) in topicDetails"
            :key="topic.index"
            :index="topic.index"
            :items="items"
            :stt="stt"
            :types="types"
            @delete="deleteHandler"/>
        <button class="btn btn-primary" id="more" @click.prevent="clicked">+</button>
    </div>
</template>

<script>
    import TopicDetail from './TopicDetail';
    import axios from 'axios';

    export default {
        name: "TopicBuilder",
        components: {
            TopicDetail
        },
        data() {
            return {
                topicDetails: [],
                types: [],
                items: [],
            }
        },
        methods: {
            clicked() {
                this.topicDetails.push({
                    index: `${Math.random()}+${Math.random()}`,
                    type_id: null,
                    itemSelecteds: [],
                });
            },
            deleteHandler(index) {
                let currentTopicDetailIndex = this.topicDetails.findIndex(element => {
                    return element.index === index;
                });
                this.topicDetails.splice(currentTopicDetailIndex, 1);
            },
            async getListTypes() {
                const resourceTypes = `/api-admin/types/`;
                try {
                    const {data} = await axios.get(resourceTypes);
                    this.types = data.data;
                } catch (error) {
                    alert("Lấy danh sách Type thất bại vui lòng F5 lại");
                }
            },
            async getListItems() {
                const resourceItems = `/api-admin/items/`;
                try {
                    const {data} = await axios.get(resourceItems);
                    this.items = data.data;
                } catch (error) {
                    alert("Lấy danh sách Item thất bại vui lòng F5 lại");
                }
            }
        },
        created() {
            this.getListTypes();
            this.getListItems();
        }
    }
</script>

<style scoped>
    #root {
        width: 80%;
        margin: 0 auto;
    }

    #more {
        margin-bottom: 5%;
    }
</style>
