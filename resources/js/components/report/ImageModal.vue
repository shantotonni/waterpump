<template>
  <modal name="modal" :width="600" height="auto" :adaptive="false" :shiftY="0.6" :scrollable="true" :style="{zIndex:9999}">
    <div class="col-md-12" style="padding-bottom:10px;">
      <h5 style="text-align:center;padding-top: 10px;"><b>{{title}} (Service Master: {{id}})</b></h5>
      <div v-for="(image,index) in images" :key="index" style="text-align:center;margin-bottom: 5px;">
        <img :src="`${image.image}`" width="500" height="400" alt="image">
      </div>
    </div>
  </modal>
</template>

<script>
import {bus} from "../../app";
export default {
  name: "ImageModal",
  data() {
    return {
      id: '',
      type: '',
      base_url: '/waterpump/public',
      images: [],
      title: ''
    }
  },
  mounted () {
    let instance = this;
    bus.$on('imageModalShow',function (id,type) {
      instance.$modal.show('modal')
      instance.id = id;
      instance.type = type;
      instance.getData(id,type);
    });
  },
  methods: {
    getData(id,type) {
      let config = {
        header: {
          'Authorization': 'bearer '+this.$store.state.token
        }
      };
      let instance = this;
      axios.get(this.base_url+'/api/admindashboard/getImage/'+id+'/'+type,config)
          .then(function(response) {
            instance.images = [];
            let remote_url = 'https://app.acibd.com/apps/waterpump/public/uploads/';
            if (response.data.imgType === 'SelfWarrantyCardImage') {
              instance.title = 'Warranty Card Image';
              response.data.data.forEach(function (item) {
                instance.images.push({
                  image: remote_url + item.WarrentyCardImage
                });
              });
            }
            if (response.data.imgType === 'OutsourceWarrantyCardImage') {
              instance.title = 'Warranty Card Image';
              response.data.data.forEach(function (item) {
                instance.images.push({
                  image: remote_url + item.WarrentyCardImage
                });
              });
            }
            if (response.data.imgType === 'SelfBillImage') {
              instance.title = 'Bill Image';
              response.data.data.forEach(function (item) {
                instance.images.push({
                  image: remote_url + item.Image
                });
              });
            }
            if (response.data.imgType === 'OutsourceBillImage') {
              instance.title = 'Bill Image';
              response.data.data.forEach(function (item) {
                instance.images.push({
                  image: remote_url + item.Image
                });
              });
            }
          }).catch(function (error){
      })
    },
  }
}
</script>

<style scoped>

</style>