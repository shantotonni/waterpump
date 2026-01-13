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
            <h3>Daily Spare Parts Report</h3>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><router-link :to="{name: 'Home'}">Home</router-link></li>
              <li class="breadcrumb-item active">Daily Spare Parts Report</li>
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
                <button type="submit" @click.prevent="dailyReport()" class="btn btn-default button-border"><i class="fas fa-filter"></i> Filter</button>
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
                            name    = "daily-spare-parts-report.xls">
                            Excel
                        </export-excel>
                    </a>

                </div>
                <div class="col-md-4 input-group mb-3" style="padding-right: 0px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">Search</span>
                    </div>
                    <input class="form-control" type="text" v-model="tableData.search" @input="dailyReport()">
                </div>
            </div>
            <div>
                <data-table :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" @sort="sortBy" id="printMe">
                    <tbody>
                        <tr v-for="(data, index) in reportData" :key="index">
                            <td>{{data.SL}}</td>
                            <td>{{data.staffid}}</td>
                            <td>{{data.StaffName}}</td>
                            <td>{{data.TerritoryName}}</td>
                            <td>{{data.SparePartsCode}}</td>
                            <td>{{data.SparePartsName}}</td>
                            <td>{{data.Opening}}</td>
                            <td>{{data.Recive}}</td>
                            <td>{{data.TotalQuantity}}</td>
                            <td>{{data.UsedQuantity}}</td>
                            <td>{{data.Closing}}</td>
                        </tr>
                    </tbody>
                </data-table>
            </div>
          </div>
          <div class="col-md-12 input-group mb-3 mt-3">
            <div class="col-md-6 invisible">
                <div class="col-md-4 input-group-prepend justify-content-center">
                    <span class="mt-2">Show:</span>&nbsp;<select class="form-control" v-model="tableData.length" @change="dailyReport()">
                        <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 d-flex flex-row-reverse">
                <pagination :pagination="pagination"
                        @prev="dailyReport(pagination.prevPageUrl)"
                        @next="dailyReport(pagination.nextPageUrl)">
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
        {width: '20%', label:'SL', name:'SL'},
        {width: '20%', label:'StaffID', name:'staffid'},
        {width: '20%', label:'StaffName', name:'username'},
        {width: '20%', label:'Territory', name:'TerritoryName'},
        {width: '20%', label:'SparePartsCode', name:'SparePartsCode'},
        {width: '20%', label:'SparePartsName', name:'ProductName'},
        {width: '20%', label:'Opening', name:'Opening'},
        {width: '20%', label:'Receive', name:'Recive'},
        {width: '20%', label:'TotalQuantity', name:'TotalQuantity'},
        {width: '20%', label:'UsedQuantity', name:'UsedQuantity'},
        {width: '20%', label:'PresentStock/Closing', name:'PresentStock'}
      ];
      columns.forEach((column) => {
          sortOrders[column.name] = 1;
      });
    return {
        base_url: '/waterpump/public',
      reportData: [],
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
      masterID: {
          serviceMasterId:''
      },
      json_fields: {
            'SL': 'SL',
            'Staff ID': 'staffid',
            'Staff Name': 'StaffName',
            'Territory': 'TerritoryName',
            'Spare Parts Code': 'SparePartsCode',
            'Spare Parts Name': 'SparePartsName',
            'Opening': 'Opening',
            'Recive': 'Recive',
            'Total Quantity': 'TotalQuantity',
            'Used Quantity': 'UsedQuantity',
            'Present Stock': 'Closing',
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
            this.dailyReport();
        }else{
            this.isAuthenticate = false;
            this.$router.push(this.base_url+"/login");
        }
    },
  mounted() {
    this.loader = true;
    this.exportReport();
  },
  methods: {
    dailyReport(url=this.base_url+"/api/admindashboard/dailysparepartsreport") {
        this.tableData.draw++;
      axios
        .get(url, { params: this.tableData }, { token: this.$store.state.token })
        .then((response) => {
            console.log(response);
            this.loader = false;
            if(this.tableData.draw == response.data.dailySparePartsReport.draw){
                this.reportData = response.data.dailySparePartsReport.data.data;
                this.json_data = response.data.dailySparePartsReport.data.data;
                // this.serviceDetails = response.data.serviceMaster.details;
                // this.serviceDetails = response.data.serviceDetails;
                this.configPagination(response.data.dailySparePartsReport.data);
            }
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
        this.dailyReport();
    },
    getIndex(array, key, value){
        return array.findIndex(i => i[key] == value)
    },
    clearFilter() {
        this.tableData.fromDate = '';
        this.tableData.toDate = '';
        this.dailyReport();
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
                .get(this.base_url+'/api/admindashboard/exportDailySparePartsReport', { params: this.tableData }, { token: this.$store.state.token })
                .then((response) => {
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
