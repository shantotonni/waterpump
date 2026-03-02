<style scoped>
.popper{
    padding: 20px 10px 15px 10px !important;
    text-align: left !important;
}
.button-border{
    border-color:#0042ff
}
</style>

<template>
<div v-if="isAuthenticate">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h3>Provided Services</h3>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><router-link :to="{name: 'Home'}">Home</router-link></li>
              <li class="breadcrumb-item active">Provided Services</li>
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
            <form class="form-inline">
            <div class="form-group">
                <p class="form-control-static">From Date</p>
            </div>
            <div class="form-group mx-sm-3">
                <input class="form-control" type="date" v-model="tableData.fromDate">
            </div>
            <div class="form-group">
                <p class="form-control-static">To Date</p>
            </div>
            <div class="form-group mx-sm-3">
                <input class="form-control" type="date" v-model="tableData.toDate">
            </div>
            <div class="form-group">
                <button type="submit" @click.prevent="allService()" class="btn btn-default button-border"><i class="fas fa-filter"></i> Filter</button>
            </div>
            <div class="form-group mx-sm-3">
                <button type="submit" @click.prevent="clearFilter()" class="btn btn-default button-border">Clear Filter</button>
            </div>
            </form>
          </div>
          <div class="col-md-12">
            <div class="input-group">
                <div class="col-md-8" style="padding-left: 0px;">
                    <button class="btn btn-default button-border" @click.prevent="print"><i class="fas fa-print"></i> Print</button>
                    <a @click.prevent="exportReport">
                        <export-excel
                            class   = "btn btn-default button-border"
                            :data   = "json_data"
                            :fields = "json_fields"
                            worksheet = "My Worksheet"
                            name    = "service-summary-report.xls">
                            Excel
                        </export-excel>
                    </a>
                </div>
                <div class="col-md-4 input-group mb-3" style="padding-right: 0px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">Search</span>
                    </div>
                    <input class="form-control" type="text" v-model="tableData.search" @input="allService()">
                </div>
            </div>
            <div>
                <data-table :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" @sort="sortBy" id="printMe">
                    <tbody>
                        <tr v-for="providedService in providedServiceList" :key="providedService.ServiceMasterID" v-if="providedServiceList.length">
                            <td>{{providedService.ServiceMasterID}}</td>
                            <td>{{providedService.StaffID}}</td>
                            <td>{{providedService.StaffName}}</td>
                            <td>{{providedService.CustomerName}}</td>
                            <td>{{providedService.Mobile}}</td>
                            <td>{{providedService.DistrictName}}</td>
                            <td>{{providedService.TTYName}}</td>
                            <td>{{providedService.AttendDate}}</td>
                            <td v-if="providedService.Point == 0">
                                <popper
                                    trigger="clickToOpen"
                                    :options="{
                                    placement: 'bottom',
                                    modifiers: { offset: { offset: '0,10px' } }
                                    }">
                                    <div class="popper" style="background-color:#3498db;color:white;font-size: 9pt;">
                                    <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" v-bind:name="providedService.StaffID+providedService.ServiceMasterID" v-model="point.Point" value="5">Satisfied
                                    </label>
                                    </div>
                                    <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" v-bind:name="providedService.StaffID+providedService.ServiceMasterID" v-model="point.Point" value="4">Moderately Satisfied
                                    </label>
                                    </div>

                                    <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" v-bind:name="providedService.StaffID+providedService.ServiceMasterID" v-model="point.Point" value="3">Dissatisfied / Others
                                    </label>
                                    </div>

                                    <div class="mt-3 text-right">
                                        <button @click.prevent="addPoint(providedService)" type="button" id="pointadd" class="btn btn-success btn-sm">Confirm</button>
                                    </div>
                                    </div>
                                    <a slot="reference" style="cursor:pointer;color:#007bff;">Rate&nbsp;Now</a>

                                </popper>
                            </td>
                            <td v-else>{{providedService.Feedback}}</td>
                          <td v-if="!providedService.Remarks">
                            <popper
                                trigger="clickToOpen" :options="{
                                    placement: 'bottom',
                                    modifiers: { offset: { offset: '0,10px' } }
                                    }">
                              <div class="popper" style="background-color:#3498db;color:white;font-size: 9pt;">
                                  <div class="form-check">
                                    <select name="" id="" name="Remarks" v-model="Remarks" class="form-control">
                                      <option>Select Remarks</option>
                                      <option :value="remark.Remarks" v-for="(remark , index) in remarks" :key="index">{{ remark.Remarks }}</option>
                                    </select>
                                    <div class="mt-3 text-right">
                                      <button type="button" @click="addRemarks(providedService.ServiceMasterID)" id="dee" class="btn btn-success btn-sm">Confirm</button>
                                    </div>
                                </div>
                              </div>
                              <a slot="reference" style="cursor:pointer;color:#007bff;">Remarks</a>

                            </popper>
                          </td>
                            <td v-else>{{ providedService.Remarks }}</td>
                            <td v-if="providedService.Point==0">0</td>
                            <td v-else>{{ providedService.Point }}</td>
                            <td>{{ providedService.EntryDate }}</td>
                        </tr>
                    </tbody>
                </data-table>
            </div>

          </div>

          <div class="col-md-12 input-group mb-3">
            <div class="col-md-6 invisible">
                <div class="col-md-4 input-group-prepend justify-content-center">
                    <span class="mt-2">Show:</span>&nbsp;<select class="form-control" v-model="tableData.length" @change="allService()">
                        <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 d-flex flex-row-reverse mt-3">
                <pagination :pagination="pagination"
                        @prev="allService(pagination.prevPageUrl)"
                        @next="allService(pagination.nextPageUrl)">
                </pagination>
            </div>
          </div>
      </div>
    </section>
  </div>
</div>
<div class="col-md-12 mb-3 mt-3" v-else>Please Login</div>
</template>

<script>
import DataTable from '../datatable/DataTable';
import Pagination from '../pagination/Pagination';
import Popper from 'vue-popperjs';
import 'vue-popperjs/dist/vue-popper.css';
export default {
    components: {
        'data-table': DataTable,
        'pagination': Pagination,
        'popper'    : Popper
    },
  data:() => {
      let sortOrders = {};
      let columns = [
        {width: '10%', label:'SL', name:'ServiceMasterID'},
        {width: '10%', label:'Staff ID', name:'StaffID'},
        {width: '20%', label:'Staff Name', name:'UserName'},
        {width: '20%', label:'Customer Name', name:'CustomerName'},
        {width: '20%', label:'Customer Mobile', name:'CustomerMobile'},
        {width: '20%', label:'District', name:'District'},
        {width: '20%', label:'Territory', name:'Territory'},
        {width: '20%', label:'Attend Date', name:'AttendDate'},
        {width: '20%', label:'Feedback', name:'Feedback'},
        {width: '20%', label:'Remarks', name:'Remarks'},
        {width: '20%', label:'Points', name:'Point'},
        {width: '20%', label:'Entry Date & Time', name:'EntryDate'},
      ];
      columns.forEach((column) => {
          sortOrders[column.name] = 1;
      });
    return {
      base_url: '/waterpump/public',
      providedServiceList: [],
      serviceDetails: [],
      columns:columns,
      sortKey: 'StaffID',
      sortOrders:sortOrders,
      perPage: ['20', '30', '40'],
      tableData:{
          draw:0,
          length:20,
          search:'',
          column: 0,
          dir:'desc',
          fromDate: '',
          toDate: ''
      },
      pagination:{
          lastPage:'',
          currentPage:'',
          total:'',
          lastPageUrl:'',
          nextPageUrl:'',
          prevPageUrl:'',
          from:'',
          to:''
      },
      isAuthenticate: '',
      loader: false,
      opened: [],
      expand: false,
      Remarks:'',

      masterID: {
          serviceMasterId:''
      },
      point: {
          StaffID: '',
          ServiceMasterID: '',
          Point: '',
          EntryBy: ''
      },
      json_fields: {
            'SL': 'ServiceMasterID',
            'Staff ID': 'StaffID',
            'Staff Name': 'StaffName',
            'Customer Name': 'CustomerName',
            'Customer Mobile': 'Mobile',
            'District': 'DistrictName',
            'Territory': 'TTYName',
            'Attend Date': 'AttendDate',
            'Feedback': 'Feedback',
            'Remarks': 'Remarks',
            'Rating point': 'Point',
            'EntryDate': 'EntryDate',
        },
      json_data: [],
      json_meta: [
            [
                {
                    'key': 'charset',
                    'value': 'utf-8'
                }
            ]
        ],
      remarks:[],
    };
  },
  created(){
        if (localStorage.getItem('auth') != null) {
            this.isAuthenticate = true;
            this.userId = this.$store.state.userid;
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.$store.state.token;
            this.allService();
        }else{
            this.isAuthenticate = false;
            this.$router.push(this.base_url+"/login");
        }
    },
  mounted() {
    this.loader = true;
    this.getAllRemarks();
  },
  methods: {
    allService(url=this.base_url+"/api/admindashboard/providedservicereport") {
        this.tableData.draw++;
      axios.get(url, { params: this.tableData }, { token: this.$store.state.token })
        .then((response) => {
            this.loader = false;
            if(this.tableData.draw == response.data.serviceMaster.draw){
                this.providedServiceList = response.data.serviceMaster.data.data;
                // this.serviceDetails = response.data.serviceDetails;
                this.configPagination(response.data.serviceMaster.data);
            }
            this.exportReport();
        })
        .catch((error) => {
            this.$toastr.error('Something went wrong.');
        });
    },
    configPagination(data){
        this.pagination.lastPage = data.last_page;
        this.pagination.currentPage = data.current_page;
        this.pagination.total = data.total;
        this.pagination.lastPage = data.last_page_url;
        this.pagination.nextPageUrl = data.next_page_url;
        this.pagination.prevPageUrl = data.prev_page_url;
        this.pagination.from = data.from;
        this.pagination.to = data.to;
    },
    sortBy(key){
        this.sortKey = key;
        this.sortOrders[key] = this.sortOrders[key] * -1;
        this.tableData.column = this.getIndex(this.columns, 'name', key);
        this.tableData.dir = this.sortOrders[key] === 1 ? 'asc' : 'desc';
        this.allService();
    },
    getIndex(array, key, value){
        return array.findIndex(i => i[key] == value)
    },
    addPoint(row, url=this.base_url+"/api/customercare/addpoint"){
        this.point.StaffID=row.StaffID
        this.point.ServiceMasterID=row.ServiceMasterID
        this.point.EntryBy=this.$store.state.userid
        $('#pointadd').prop('disabled', true);
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.$store.state.token;
        axios.post(url, this.point).then((res) => {
          if (res.data.status == true) {
            this.$toastr.success('Point added successfully');
            this.allService();
          }
        })
        .catch((err) => {
            // this.isLooggedin = false;
            this.$toastr.error('Something went wrong.');
        });
    },
    clearFilter() {
        this.tableData.fromDate = '';
        this.tableData.toDate = '';
        this.allService();
        this.exportReport();
    },
    print () {
      // Pass the element id here
      this.$htmlToPaper('printMe');
    },
    exportReport(){
        axios.get(this.base_url+'/api/admindashboard/exportServiceReport', { params: this.tableData }, { token: this.$store.state.token })
        .then((response) => {
            this.json_data = [];
            this.json_data = response.data;
        })
        .catch((error) => {
            this.$toastr.error('Something went wrong.');
        });
    },
    addRemarks(id){
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.$store.state.token;
      axios.post(this.base_url + '/api/customercare/add-remarks', {master_id:id,Remarks:this.Remarks}).then((res) => {
        //this.allService();
      })
          .catch((err) => {
            // this.isLooggedin = false;
            this.$toastr.error('Something went wrong.');
          });
    },
    getAllRemarks() {
      axios.get(this.base_url+ "/api/admindashboard/get-all-remarks").then((response) => {
        this.remarks = response.data.remarks
          }).catch((error) => {
          });
    },
  },
};
</script>
