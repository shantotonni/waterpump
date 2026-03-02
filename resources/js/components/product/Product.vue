<template>
<div v-if="isAuthenticate">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h3>Product</h3>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><router-link :to="{ name: 'Home' }">Home</router-link></li>
              <li class="breadcrumb-item active">Product</li>
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
                <div class="input-group" style="padding-left: 0px;">
                    <div class="col-md-8">
                        <div class="input-group-prepend">
                            <button class="btn btn-primary mb-3" @click="createUser()">Create New</button>
                        </div>
                    </div>
                    <div class="col-md-4 input-group mb-3" style="padding-right: 0px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">Search</span>
                        </div>
                        <input class="form-control" type="text" v-model="tableData.search" @input="userList()">
                    </div>
                </div>
                <data-table :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" @sort="sortBy">
                    <tbody>
                        <tr v-for="user in usersList" :key="user.UserID">
                            <td><a v-if="user.active=='Y'" @click.prevent="lockUser(user.StaffID, false)" style="cursor:pointer;color:#3490dc;">Lock</a>
                                <a v-else @click.prevent="lockUser(user.StaffID, true)" style="cursor:pointer;color:#3490dc;">UnLock</a> |
                                <a @click.prevent="editUser(user)" style="cursor:pointer;color:#3490dc;">Edit</a></td>
                            <td>{{user.UserID}}</td>
                            <td>{{user.StaffID}}</td>
                            <td>{{user.UserName}}</td>
                            <td>{{user.TerritoryName}}</td>
                            <td>{{user.DistrictName}}</td>
                            <td>{{user.MobileNo}}</td>
                            <td v-if="user.TotalPoint!=null">{{parseFloat(user.TotalPoint)}}</td>
                            <td v-else>0</td>
                            <td>{{user.UserTypeName}}</td>
                            <td v-if="user.active=='Y'"><p class="activestatus">active</p></td>
                            <td v-else><p class="inactivestatus">inactive</p></td>

                        </tr>
                    </tbody>
                </data-table>
            </div>
            <div class="col-md-12 input-group">
                <div class="col-md-6 invisible">
                    <div class="col-md-4 input-group-prepend justify-content-center">
                       <span class="mt-2">Show:</span>&nbsp;<select class="form-control" v-model="tableData.length" @change="userList()">
                            <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 d-flex flex-row-reverse">
                    <pagination :pagination="pagination"
                            @prev="userList(pagination.prevPageUrl)"
                            @next="userList(pagination.nextPageUrl)">
                    </pagination>
                </div>
            </div>
            <div>

            </div>

        </div>
      </div>

    <div ref="modal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" v-if="isInsert">Create User</h5>
                <h5 class="modal-title" v-if="!isInsert">Update User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
             <form @submit.prevent="saveUser" @keydown="form.onKeydown($event)">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12"><p><span class="required-field"><b>*</b></span> Required Field</p></div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username"><span class="required-field">*</span> Name</label>
                            <input type="text" class="form-control" id="username" name="username" v-model="form.username"  placeholder="Enter Staff Name"
                                                :class="{ 'is-invalid': form.errors.has('username') }">
                            <has-error :form="form" field="username"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="staffid"><span class="required-field">*</span> Staff ID</label>
                            <input type="text" class="form-control" id="staffid" name="staffid" v-model="form.staffid" placeholder="This will be the userid"
                                                :class="{ 'is-invalid': form.errors.has('staffid') }" v-if="isInsert">
                            <input type="text" class="form-control" id="staffid" name="staffid" v-model="form.staffid" placeholder="This will be the userid"
                                                :class="{ 'is-invalid': form.errors.has('staffid') }" v-else readonly>
                            <has-error :form="form" field="staffid"></has-error>
                        </div>
                        <div class="form-group" v-if="isInsert">
                            <label for="password"><span class="required-field">*</span> Password</label>
                            <input type="text" class="form-control" id="password" name="password" v-model="form.password" placeholder="Password"
                                                :class="{ 'is-invalid': form.errors.has('password') }">
                            <has-error :form="form" field="password"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="mobile"><span class="required-field">*</span> Mobile No.</label>
                            <input type="text" class="form-control" id="mobile" name="mobile" v-model="form.mobile" placeholder="Mobile Number"
                                                :class="{ 'is-invalid': form.errors.has('mobile') }">
                            <has-error :form="form" field="mobile"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" class="form-control" id="dob" name="dob" v-model="form.dob" placeholder="Date of Birth"
                                                :class="{ 'is-invalid': form.errors.has('dob') }">
                            <has-error :form="form" field="dob"></has-error>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="address"><span class="required-field">*</span> Address</label>
                            <input type="text" class="form-control" id="address" name="address" v-model="form.address" placeholder="Address"
                                                :class="{ 'is-invalid': form.errors.has('address') }">
                            <has-error :form="form" field="address"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="nid">NID</label>
                            <input type="text" class="form-control" id="nid" name="nid" v-model="form.nid" placeholder="NID"
                                                :class="{ 'is-invalid': form.errors.has('nid') }">
                            <has-error :form="form" field="nid"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="territorycode"><span class="required-field">*</span> Territory</label>
                            <select name="territorycode" id="territorycode" v-model="form.territorycode" class="form-control"
                                                    :class="{ 'is-invalid': form.errors.has('territorycode') }">
                                <option value="" selected disabled>Select Territory</option>
                                <option v-for="(territory, index) in territoryList" :key="index" :value="territory.TTYCode">{{territory.TTYName}}</option>
                            </select>
                            <has-error :form="form" field="territorycode"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="districtcode"><span class="required-field">*</span> District</label>
                            <select name="districtcode" id="districtcode" v-model="form.districtcode" class="form-control" :searchable="true"
                                            :class="{ 'is-invalid': form.errors.has('districtcode') }">
                                <option value="" selected disabled>Select District</option>
                                <option v-for="(district, index) in districtList" :key="index" :value="district.DistrictCode">{{district.DistrictName}}</option>
                            </select>
                            <has-error :form="form" field="districtcode"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="usertype"><span class="required-field">*</span> User Type</label>
                            <select name="usertype" id="usertype" v-model="form.usertype" class="form-control"
                                                    :class="{ 'is-invalid': form.errors.has('usertype') }">
                                <option value="" disabled>Select UserType</option>
                                <option value="A">Admin</option>
                                <option value="U" selected>User</option>
                                <option value="CC">Customer Care</option>
                            </select>
                            <has-error :form="form" field="usertype"></has-error>
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
export default {
    components: {
        'data-table': DataTable,
        'pagination': Pagination,
    },
    data: () => {
      let sortOrders = {};
      let columns = [
        {label:'Action', name:'Edit'},
        {label:'SL', name:'id'},
        {label:'Staff ID', name:'staffid'},
        {label:'Staff Name', name:'username'},
        {label:'Territory', name:'territory'},
        {label:'District Name', name:'district'},
        {label:'Mobile No.', name:'mobile'},
        {label:'Avg. Rating Point', name:'points'},
        {label:'User Type', name:'usertype'},
        {label:'Status', name:'status'},
      ];
      columns.forEach((column) => {
          sortOrders[column.name] = 1;
      });
    return {
    base_url: '/waterpump/public',
      usersList: [],
      districtList: [],
      territoryList: [],
      columns:columns,
      sortKey: 'staffid',
      sortOrders:sortOrders,
      perPage: ['10', '20', '30'],
      tableData:{
          draw:0,
          length:10,
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
            username: '',
            staffid: '',
            password: '',
            mobile: '',
            dob: "",
            address: '',
            nid: '',
            territorycode: '',
            districtcode: '',
            usertype: '',
            created_by: '',
            updated_by: '',
        }),
      isAuthenticate: '',
      loader: false,
      isInsert: true
    };
  },
    created(){
        if (localStorage.getItem('auth') != null) {
            this.isAuthenticate = true;
            this.userId = this.$store.state.userid;
            const token = localStorage.getItem('auth');
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
            this.userList();
        }else{
            this.isAuthenticate = false;
            this.$router.push({name: 'Login'});
        }
    },
  mounted() {
      this.loader = true;
      $(this.$refs.modal).on('hidden.bs.modal', () => {
        this.form.reset();
    })

  },
  methods: {
    userList(url = this.base_url+'/api/admindashboard/users') {
        this.tableData.draw++;
      axios
        .get(url, { params: this.tableData }, { token: this.$store.state.token })
        .then((response) => {
            this.loader = false;
            if(this.tableData.draw == response.data.users.draw){
                this.usersList = response.data.users.data.data;
                this.configPagination(response.data.users.data);
            }

        })
        .catch((err) => {
            this.$toastr.error('Nothing Found');
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
        this.userList();
    },
    getIndex(array, key, value){
        return array.findIndex(i => i[key] == value)
    },
    getServiceDetails() {
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.$store.state.token;
        axios
        .get(this.base_url+"/api/service/servicedetails", { token: this.$store.state.token })
        .then((res) => {
          if (res.data.status == true) {
              this.districtList = res.data.district;
              this.territoryList = res.data.territory;
          }
        })
        .catch((err) => {
        });
    },
    createUser() {
        this.isInsert = true;
        $('.modal').modal();
        this.getServiceDetails();
    },
    saveUser() {
        if(this.isInsert){
        this.form.created_by = this.$store.state.userid;
        }else{
        this.form.updated_by = this.$store.state.userid;
        }
        this.form.post(this.base_url+'/api/auth/register')
        .then((response)=>{
                this.form.reset();
                $('.modal').modal('hide');
                this.$toastr.success('User created successfully');
                this.userList();

        }).catch((error)=>{
            this.$toastr.error('Something went wrong. Please try again');
        })
    },
    editUser(user) {
        this.isInsert = false;
        this.getServiceDetails();
        this.form.username = user.UserName;
        this.form.password = '123456';
        this.form.staffid = user.StaffID;
        this.form.mobile = user.MobileNo;
        this.form.address = user.Address;
        this.form.nid = user.NID;
        this.form.dob = user.DateOfBirth;
        this.form.territorycode = user.TerritoryCode;
        this.form.districtcode = user.DistrictCode;
        this.form.usertype = user.UserType;
        $('.modal').modal();
    },
    lockUser(staffId, unlock){
        this.form.staffid = staffId;
        if(unlock==true){
        this.form.active = 'Y';
        }else{
        this.form.active = 'N';
        }
        this.form.updated_by = this.$store.state.userid;
        this.form.post(this.base_url+'/api/auth/lockuser')
        .then((res) => {
          if (res.data.status == true) {
                if(unlock == true){
                    this.$toastr.success('User unlocked successfully');
                }else{
                    this.$toastr.success('User locked successfully');
                }
                this.userList();
          }
        })
        .catch((err) => {
            this.$toastr.error('Something went wrong!');
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
</style>
