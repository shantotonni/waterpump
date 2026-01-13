<template>
<div v-if="isAuthenticate">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h3>Spare Parts Stock</h3>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><router-link :to="{ name: 'Home' }">Home</router-link></li>
              <li class="breadcrumb-item active">Spare Parts Stock</li>
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
        <div class="card-body">
            <div>
                <div class="input-group">
                    <div class="col-md-8 mb-3" style="padding-left: 0px;">
                        <button class="btn btn-default button-border" @click.prevent="print"><i class="fas fa-print"></i> Print</button>
                        <a @click.prevent="exportReport">
                            <export-excel
                                class   = "btn btn-default button-border"
                                :data   = "json_data"
                                :fields = "json_fields"
                                worksheet = "My Worksheet"
                                name    = "spare-parts-stock.xls">
                                Excel
                            </export-excel>
                        </a>
                        <!-- <button class="btn btn-default button-border" @click.prevent="exportToPDF">PDF</button> -->
                    </div>
                    <!-- <div class="col-md-2 input-group mb-3" style="padding-right: 0px;">
                        <button class="btn btn-primary">Month Closing</button>
                    </div> -->
                    <div class="col-md-8" style="padding-left: 0px;">
                        <div class="col-md-4 input-group-prepend">
                            <button class="btn btn-primary mb-3" @click="addStock()">Add New</button>
                        </div>
                    </div>
                    <div class="col-md-4 input-group mb-3" style="padding-right: 0px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">Search</span>
                        </div>
                        <input class="form-control" type="text" v-model="tableData.search" @input="stockList()">
                    </div>
                </div>
                <div ref="document">
                    <data-table :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" @sort="sortBy" id="printMe">
                        <tbody>
                            <tr v-for="list in sparePartsStockList" :key="list.StockID">
                                <td><a @click.prevent="editStock(list)" style="cursor:pointer;color:#3490dc;">Edit</a></td>
                                <td>{{list.StockID}}</td>
                                <td>{{list.StaffID}}</td>
                                <td>{{list.UserName}}</td>
                                <td>{{list.ProductCode}}</td>
                                <td>{{list.ProductName}}</td>
                                <td>{{moment(list.Period).format('MM-YYYY')}}</td>
                                <td>{{list.Opening}}</td>
                                <td>{{list.Recive}}</td>
                                <td>{{moment(list.ReceiveDate).format('DD-MM-YYYY')}}</td>

                            </tr>
                        </tbody>
                    </data-table>
                </div>

            </div>
            <div class="col-md-12 input-group">
                <div class="col-md-6 invisible">
                    <div class="col-md-4 input-group-prepend justify-content-center">
                       <span class="mt-2">Show:</span>&nbsp;<select class="form-control" v-model="tableData.length" @change="stockList()">
                            <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 d-flex flex-row-reverse">
                    <pagination :pagination="pagination"
                            @prev="stockList(pagination.prevPageUrl)"
                            @next="stockList(pagination.nextPageUrl)">
                    </pagination>
                </div>
            </div>
            <div>

            </div>

        </div>
      </div>

    <div ref="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" v-if="isInsert">Add Stock</h5>
                <h5 class="modal-title" v-if="!isInsert">Update Stock</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
             <form @submit.prevent="saveStock" @keydown="form.onKeydown($event)">
                <div class="modal-body">
                    <div class="row">
                    <div class="col-md-12"><p><span class="required-field"><b>*</b></span> Required Field</p></div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="staffid"><span class="required-field">*</span> Staff ID</label>
                            <!-- <input type="text" class="form-control" id="staffid" name="StaffID" v-model="form.StaffID" placeholder="This will be the userid"
                                                :class="{ 'is-invalid': form.errors.has('StaffID') }" v-if="isInsert">
                            <input type="text" class="form-control" id="staffid" name="StaffID" v-model="form.StaffID" placeholder="This will be the userid"
                                                :class="{ 'is-invalid': form.errors.has('StaffID') }" v-else readonly> -->
                            <select name="StaffID" id="StaffID" v-model="form.StaffID" class="form-control"
                                                    :class="{ 'is-invalid': form.errors.has('StaffID') }" v-if="isInsert">
                                <option value="" selected disabled>Select User</option>
                                <option v-for="(users, index) in usersList" :key="index" :value="users.StaffID">{{users.StaffID}} - {{users.StaffName}}</option>
                            </select>
                            <select name="StaffID" id="StaffID" v-model="form.StaffID" class="form-control"
                                                    :class="{ 'is-invalid': form.errors.has('StaffID') }" v-else disabled>
                                <option value="" selected disabled>Select User</option>
                                <option v-for="(users, index) in usersList" :key="index" :value="users.StaffID">{{users.StaffID}} - {{users.StaffName}}</option>
                            </select>
                            <has-error :form="form" field="StaffID"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="productcode"><span class="required-field">*</span> Spare Part</label>
                            <select name="ProductCode" id="sparepart" v-model="form.ProductCode" class="form-control"
                                                    :class="{ 'is-invalid': form.errors.has('ProductCode') }">
                                <option value="" selected disabled>Select Spare Part</option>
                                <option v-for="(spareParts, index) in sparePartsList" :key="index" :value="spareParts.ProductCode">{{spareParts.ProductCode}} - {{spareParts.ProductName}}</option>
                            </select>
                            <has-error :form="form" field="ProductCode"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="period"><span class="required-field">*</span> Period</label>
                            <input type="month" class="form-control" id="period" name="Period" v-model="form.Period" placeholder="This will be the period"
                                                :class="{ 'is-invalid': form.errors.has('Period') }" v-if="isInsert">
                            <input type="month" class="form-control" id="period" name="Period" v-model="form.Period" placeholder="This will be the period"
                                                :class="{ 'is-invalid': form.errors.has('Period') }" v-else readonly>
                            <has-error :form="form" field="Period"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="opening">Opening</label>
                            <input type="text" class="form-control" id="opening" name="Opening" v-model="form.Opening" placeholder="This will be the opening"
                                                :class="{ 'is-invalid': form.errors.has('Opening') }">
                            <has-error :form="form" field="Opening"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="receive"><span class="required-field">*</span> Receive</label>
                            <input type="text" class="form-control" id="receive" name="Recive" v-model="form.Recive" placeholder="This will be the receive"
                                                :class="{ 'is-invalid': form.errors.has('Recive') }">
                            <has-error :form="form" field="Recive"></has-error>
                        </div>
                    </div>
                </div>


                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" :disabled="form.busy" v-if="isInsert">Save</button>
                    <button type="submit" class="btn btn-primary" :disabled="form.busy" v-else>Save changes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    </section>
  </div>
</div>
<div v-else>Please Login</div>
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
    data: () => {
      let sortOrders = {};
      let columns = [
        {label:'Action', name:'Edit'},
        {label:'SL', name:'StockID'},
        {label:'Staff ID', name:'StaffID'},
        {label:'Staff Name', name:'UserName'},
        {label:'Spare Parts Code', name:'ProductCode'},
        {label:'Spare Parts Name', name:'ProductName'},
        {label:'Period', name:'Period'},
        {label:'Opening', name:'Opening'},
        {label:'Recive', name:'Recive'},
        {label:'Receive Date', name:'ReceiveDate'}
      ];
      columns.forEach((column) => {
          sortOrders[column.name] = 1;
      });
    return {
    base_url: '/waterpump/public',
      sparePartsStockList: [],
      sparePartsList: [],
      usersList: [],
      columns:columns,
      sortKey: 'staffid',
      sortOrders:sortOrders,
      perPage: ['50', '100', '200'],
      tableData:{
          draw:0,
          length:50,
          search:'',
          column: 0,
          dir:'desc',
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
        form: new Form ({
            StaffID: '',
            ProductCode: '',
            Period: '',
            Opening: "",
            Recive: '',
            EntryBy: '',
            EditBy: '',
        }),
      isAuthenticate: '',
      loader: false,
      isInsert: true,
      json_fields: {
            'SL': 'SL',
            'Staff ID': 'StaffID',
            'Staff Name': 'UserName',
            'Spare Parts Code': 'ProductCode',
            'Spare Parts Name': 'ProductName',
            'Period': 'Period',
            'Opening': 'Opening',
            'Recive': 'Recive',
            'ReceiveDate': 'ReceiveDate',
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
            // console.log(localStorage.getItem('auth'));
            const token = localStorage.getItem('auth');
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
            this.stockList();
        }else{
            this.isAuthenticate = false;
            this.$router.push(this.base_url+"/login");
        }
    },
  mounted() {
      this.loader = true;
      $(this.$refs.modal).on('hidden.bs.modal', () => {
        this.form.reset();
    })
    this.exportReport();
  },
  methods: {
    stockList(url = this.base_url+'/api/stock/stockList') {
        this.tableData.draw++;
        // console.log(this.tableData);
      axios
        .get(url, { params: this.tableData }, { token: this.$store.state.token })
        .then((response) => {
            this.loader = false;
            console.log(response.data.stockList);
            if(this.tableData.draw == response.data.stockList.draw){
                // console.log(response.data.users.draw);
                this.sparePartsStockList = response.data.stockList.data.data;
                this.configPagination(response.data.stockList.data);
                // console.log(response.data.users.data);
            }

        })
        .catch((err) => {
            this.$toastr.error('Nothing Found');
            // console.log(err);
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
        this.stockList();
    },
    getIndex(array, key, value){
        return array.findIndex(i => i[key] == value)
    },
    getServiceDetails() {
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.$store.state.token;
        axios
        .get(this.base_url+"/api/service/servicedetails", { token: this.$store.state.token })
        .then((res) => {
        //   console.log(res.data);
          if (res.data.status == true) {
              this.sparePartsList = res.data.spareParts;
            //   console.log(this.territoryList);
          }
        })
        .catch((err) => {
          console.log("Error!");
        });
    },
    addStock() {
        this.isInsert = true;
        $('.modal').modal();
        this.getServiceDetails();
        this.getStaffIds();
    },
    getStaffIds(){
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.$store.state.token;
        axios
        .get(this.base_url+"/api/stock/staffinfo", { token: this.$store.state.token })
        .then((res) => {
          console.log(res.data);
          if (res.data.status == true) {
              this.usersList = res.data.userinfo;
            //   console.log(this.territoryList);
          }
        })
        .catch((err) => {
          console.log("Error!");
        });
    },
    saveStock() {
        if(this.isInsert){
        this.form.EntryBy = this.$store.state.userid;
        }else{
        this.form.EditBy = this.$store.state.userid;
        }
        console.log(this.form);
        this.form.post(this.base_url+'/api/stock/addstock')
        .then((response)=>{
                this.form.reset();
                $('.modal').modal('hide');
                this.$toastr.success('Stock Added successfully');
                this.stockList();

        }).catch((error)=>{
            this.$toastr.error('Something went wrong. Please try again');
            console.log(error);
        })
    },
    editStock(list) {
        console.log(list);
        this.isInsert = false;
        this.getServiceDetails();
        this.getStaffIds();
        this.form.StaffID = list.StaffID;
        this.form.ProductCode = list.ProductCode;
        this.form.Period = moment(list.Period).format('YYYY-MM')
        this.form.Opening = list.Opening;
        this.form.Recive = list.Recive;
        $('.modal').modal();
    },
    moment: function (date) {
      return moment(date);
    },
    print () {
      // Pass the element id here
      this.$htmlToPaper('printMe', null, () => {
        // console.log('Printing completed or was cancelled!');
     });
    },
    exportReport(){
        axios
        .get(this.base_url+'/api/stock/exportStockReport', { params: this.tableData }, { token: this.$store.state.token })
        .then((response) => {
            this.json_data = response.data;
            console.log(response)
        })
        .catch((error) => {
            this.$toastr.error('Something went wrong.');
            console.log(error);
        });
    },
  }
};
</script>

<style scoped>
.activestatus{
    color: green;
    font-weight: 600;
}
.inactivestatus{
    color: red;
    font-weight: 600;
}
.required-field{
    color: red;
}
.button-border{
    border-color:#0042ff
}
</style>
