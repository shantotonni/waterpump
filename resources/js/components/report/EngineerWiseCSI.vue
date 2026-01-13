<template>
  <div v-if="isAuthenticate">
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h3>Engineer Wise CSI Rating</h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                  <router-link :to="{name: 'Home'}">Home</router-link>
                </li>
                <li class="breadcrumb-item active">Engineer Wise CSI Rating</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="text-center" v-if="loader">
          <div class="spinner-border" role="status">
            <span class="sr-only"><p style="color:red">Loading...</p></span>
          </div>
        </div>
        <div class="card" v-else>
          <div class="col-md-12 mb-5"></div>
          <div class="col-md-12 mb-3">
            <form class="form-inline" @submit.prevent="allService()">
              <div class="form-group">
                <p class="form-control-static">Period</p>
              </div>
              <div class="form-group mx-sm-3">
                <input type="month" class="form-control" v-model="period">
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-default button-border"><i
                    class="fas fa-filter"></i> Filter
                </button>
              </div>
            </form>
          </div>
          <div class="col-md-12">
            <div class="input-group">
              <div class="col-md-8" style="padding-left: 0px;">
                <a @click.prevent="exportReport">
                  <export-excel
                      class="btn btn-default button-border"
                      :data="json_data"
                      :fields="json_fields"
                      worksheet="My Worksheet"
                      :name="`engineer-wise-csi-report-of-${period}.xls`">
                    Excel
                  </export-excel>
                </a>
                <!-- <button class="btn btn-default button-border" @click.prevent="exportToPDF">PDF</button> -->
              </div>
            </div>
            <div ref="document" class="table-responsive" style="margin-top: 20px;">
              <table class="table table-bordered table-striped dt-responsive nowrap dataTable no-footer dtr-inline table-sm small">
                <tr>
                  <th>Engineer Name</th>
                  <th>Staff ID</th>
                  <th>No Of Services</th>
                  <th>Individual CSI</th>
                  <th>CSI</th>
                  <th>Out Of</th>
                </tr>
                <template v-if="data.length > 0">
                  <tr v-for="(item,index) in data" :key="index">
                    <td>{{ item.EngineerName }}</td>
                    <td>{{ item.staff_id }}</td>
                    <td>{{ item.noOfService }}</td>
                    <td>{{ item.IndividualCSI }}</td>
                    <td>{{ item.CSI }}</td>
                    <td>{{ item.OutOf }}</td>
                  </tr>
                </template>
                <template v-else>
                  <tr>
                    <td style="text-align: center;" colspan="6">No Data</td>
                  </tr>
                </template>
              </table>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <div class="col-md-12 mb-3 mt-3" v-else>Please Login</div>
</template>

<script>
import moment from 'moment'
import html2pdf from 'html2pdf.js'

export default {
  components: {
  },
  data: () => {
    return {
      base_url: '/waterpump/public',
      data: [],
      period: moment().format('YYYY-MM'),
      loader: false,
      json_fields: {
        'Engineer Name': 'EngineerName',
        'Staff ID': 'staff_id',
        'No Of Services': 'noOfService',
        'Individual CSI': 'IndividualCSI',
        'CSI': 'CSI',
        'Out Of': 'OutOf'
      },
      json_data: []
    };
  },
  created() {
    if (localStorage.getItem('auth') != null) {
      this.isAuthenticate = true;
      this.userId = this.$store.state.userid;
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.$store.state.token;
    } else {
      this.isAuthenticate = false;
      this.$router.push(this.base_url + "/login");
    }
  },
  mounted() {
    this.loader = true;
    this.allService();
  },
  methods: {
    allService() {
      let url = this.base_url + "/api/admindashboard/engineerWiseCSI"
      let month = this.period
      this.period = moment(this.period).format('YYYYMM')
      this.loader = true
      axios.get(url, {params: {period: this.period}}, {token: this.$store.state.token})
          .then((response) => {
            this.loader = false
            this.data = response.data.data
            this.json_data = response.data.data
            this.period = month
          })
          .catch((error) => {
            this.$toastr.error('Something went wrong.');
          });
    },
    exportReport() {
      axios.get(this.base_url + '/api/admindashboard/engineerWiseCSI',  {period: this.period}, {token: this.$store.state.token}).then((response) => {
        this.json_data = response.data.data;
      }).catch((error) => {
        this.$toastr.error('Something went wrong.');
        console.log(error);
      });
    },
    moment: function (date) {
      return moment(date);
    }
  },
};
</script>

<style scoped>
.button-border {
  border-color: #0042ff
}
</style>
