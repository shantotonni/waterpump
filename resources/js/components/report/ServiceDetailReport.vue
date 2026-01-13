<style scoped>
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
            <h3>Provided Service Detail Report</h3>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><router-link :to="{name: 'Home'}">Home</router-link></li>
              <li class="breadcrumb-item active">Provided Service Detail Report</li>
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
                <p class="form-control-static">Service Time</p>
            </div>
            <div class="form-group mx-sm-3">
                <select class="form-control" v-model="tableData.serviceTime">
                    <option value="">All</option>
                    <option value="First">First</option>
                    <option value="Second">Second</option>
                    <option value="Third">Third</option>
                    <option value="Fourth">Fourth</option>
                </select>
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
                            name    = "service-detail-report.xls">
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
                        <tr v-for="(providedService, index) in providedServiceList" :key="index">
                            <td>{{providedService.ServiceMasterID}}</td>
                            <td>{{providedService.StaffID}}</td>
                            <td>{{providedService.StaffName}}</td>
                            <td>{{providedService.TTYName}}</td>
                            <td>{{providedService.CustomerName}}</td>
                            <td>{{providedService.DistrictName}}</td>
                            <td>{{providedService.Address}}</td>
                            <td>{{providedService.Mobile}}</td>
                            <td>{{providedService.AttendDate}}</td>
                            <td>{{providedService.BrandCode}}</td>
                            <td>{{providedService.Brandname}}</td>
                            <td>{{providedService.ActionTaken}}</td>
                            <td>{{providedService.SparePartsCode}}</td>
                            <td>{{providedService.ProductName}}</td>
                            <td>{{providedService.QuantityUsed}}</td>
                            <td>{{providedService.ServiceTime}}</td>
                            <td>{{providedService.ServiceCharge}}</td>
                            <td>{{providedService.MRNo}}</td>
                            <td>{{providedService.WarrantyCardNo}}</td>
                            <td v-if="providedService.Point==0">0</td>
                            <td v-else>{{providedService.Point}}</td>
                            <td>{{providedService.EntryDate}}</td>
                        </tr>
                    </tbody>
                </data-table>
            </div>
          </div>
          <div class="col-md-12 input-group mb-3 mt-3">
            <div class="col-md-6 invisible">
                <div class="col-md-4 input-group-prepend justify-content-center">
                    <span class="mt-2">Show:</span>&nbsp;<select class="form-control" v-model="tableData.length" @change="allService()">
                        <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 d-flex flex-row-reverse">
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
import moment from 'moment'
export default {
    components: {
        'data-table': DataTable,
        'pagination': Pagination,
    },
  data:() => {
      let sortOrders = {};
      let columns = [
        {width: '20%', label:'SL', name:'ServiceMasterID'},
        {width: '20%', label:'StaffID', name:'StaffID'},
        {width: '20%', label:'StaffName', name:'UserName'},
        {width: '20%', label:'Territory', name:'Territory'},
        {width: '20%', label:'CustomerName', name:'CustomerName'},
        {width: '20%', label:'District', name:'District'},
        {width: '20%', label:'Address', name:'Address'},
        {width: '20%', label:'CustomerMobile', name:'CustomerMobile'},
        {width: '20%', label:'AttendDate', name:'AttendDate'},
        {width: '20%', label:'ProductCode', name:'ProductCode'},
        {width: '20%', label:'ModelName', name:'Model'},
        {width: '20%', label:'ActionTaken', name:'Action'},
        {width: '20%', label:'SparePartsCode', name:'SparePartsCode'},
        {width: '20%', label:'SparePartsName', name:'ProductName'},
        {width: '20%', label:'QuantityUsed', name:'QuantityUsed'},
        {width: '20%', label:'ServiceTime', name:'ServiceTime'},
        {width: '20%', label:'ServiceCharge', name:'Service Charge'},
        {width: '20%', label:'MRNo.', name:'MRNo'},
        {width: '20%', label:'WarrantyCard No.', name:'WarrantyCardNo'},
        {width: '20%', label:'RatingPoint', name:'RattingPoint'},
        {width: '20%', label:'EntryDate', name:'EntryDate'},
      ];
      columns.forEach((column) => {
          sortOrders[column.name] = 1;
      });
    return {
        base_url: '/waterpump/public',
      providedServiceList: [],
    //   serviceDetails: [],
      columns:columns,
      sortKey: 'StaffID',
      sortOrders:sortOrders,
      perPage: ['50', '100', '200'],
      tableData:{
          draw:0,
          length:50,
          search:'',
          column: 0,
          dir:'desc',
          fromDate: '',
          toDate: '',
          serviceTime: ''
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
      masterID: {
          serviceMasterId:''
      },
      json_fields: {
            'SL': 'ServiceMasterID',
            'Staff ID': 'StaffID',
            'Staff Name': 'StaffName',
            'Territory': 'TTYName',
            'Customer Name': 'CustomerName',
            'District': 'DistrictName',
            'Address': 'Address',
            'Customer Mobile': 'Mobile',
            'Attend Date': 'AttendDate',
            'Product Code': 'ModelCode',
            'Model': 'Brandname',
            'Action': 'ActionTaken',
            'SparePartsCode': 'SparePartsCode',
            'SparePartsName': 'ProductName',
            'QuantityUsed': 'QuantityUsed',
            'ServiceTime': 'ServiceTime',
            'Service Charge': 'ServiceCharge',
            'MRNo.': 'MRNo',
            'Warranty Card Number': 'WarrantyCardNo',
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
  },
  methods: {
    allService(url=this.base_url+"/api/admindashboard/providedserviceDetails") {
        this.tableData.draw++;
      axios
        .get(url, { params: this.tableData }, { token: this.$store.state.token })
        .then((response) => {
            this.loader = false;
            if(this.tableData.draw == response.data.serviceDetails.draw){
                this.providedServiceList = response.data.serviceDetails.data.data;
                this.json_data = response.data.serviceDetails.data.data;
                this.configPagination(response.data.serviceDetails.data);
            }
            this.exportReport();
        })
        .catch((error) => {
            this.$toastr.error('Something went wrong.');
            console.log(error);
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
    toggle(id) {
    	const index = this.opened.indexOf(id);
        if (index > -1) {
            this.opened.splice(index, 1)
            // this.expand = false
        } else {
            this.opened.splice(index, 1)

            this.opened.push(id)
            if(id != 0){
                this.masterID.serviceMasterId = id;
                axios
                .get(this.base_url+'/api/admindashboard/providedserviceDetails', { params: this.masterID }, { token: this.$store.state.token })
                .then((response) => {
                    if(response.data.status == true){
                        this.serviceDetails = response.data.serviceDetails;
                        // this.expand = true
                    }
                })
                .catch((error) => {
                    this.$toastr.error('Something went wrong.');
                    console.log(error);
                });
            }
        }
    },
    toggleIcon(img){
        return window.location.origin + img
    },
    clearFilter() {
        this.tableData.fromDate = '';
        this.tableData.toDate = '';
        this.allService();
        this.exportReport();
    },
    print () {
      // Pass the element id here
      this.$htmlToPaper('printMe', null, () => {
        // console.log('Printing completed or was cancelled!');
     });
    },
    exportReport(){
        axios
        .get(this.base_url+'/api/admindashboard/exportServiceDetailReport', { params: this.tableData }, { token: this.$store.state.token })
        .then((response) => {
            this.json_data = [];
            this.json_data = response.data;
            // console.log(response)
        })
        .catch((error) => {
            this.$toastr.error('Something went wrong.');
            console.log(error);
        });
    },
    moment: function (date) {
      return moment(date);
    },
  },
};
</script>
