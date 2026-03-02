<template>
<div v-if="isAuthenticate">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h3>Brand List</h3>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><router-link :to="{ name: 'Home' }">Home</router-link></li>
              <li class="breadcrumb-item active">Brand List</li>
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
                            <button class="btn btn-primary mb-3" @click="createBrand()">Create New</button>
                        </div>
                    </div>
                    <div class="col-md-4 input-group mb-3" style="padding-right: 0px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">Search</span>
                        </div>
                        <input class="form-control" type="text" v-model="tableData.search" @input="brandList()">
                    </div>
                </div>
                <data-table :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" @sort="sortBy">
                    <tbody>
                        <tr v-for="(brand, index) in brands" :key="index">
                            <td><a @click.prevent="editUser(brand)" style="cursor:pointer;color:#3490dc;">Edit</a></td>
                            <td>{{brand.SL}}</td>
                            <td>{{brand.BrandCode}}</td>
                            <td>{{brand.BrandName}}</td>
                        </tr>
                    </tbody>
                </data-table>
            </div>
            <div class="col-md-12 input-group">
                <div class="col-md-6 invisible">
                    <div class="col-md-4 input-group-prepend justify-content-center">
                       <span class="mt-2">Show:</span>&nbsp;<select class="form-control" v-model="tableData.length" @change="brandList()">
                            <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 d-flex flex-row-reverse">
                    <pagination :pagination="pagination"
                            @prev="brandList(pagination.prevPageUrl)"
                            @next="brandList(pagination.nextPageUrl)">
                    </pagination>
                </div>
            </div>
            <div>

            </div>

        </div>
      </div>

    <div ref="modal" class="modal fade bd-example-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" v-if="isInsert">Create Brand</h5>
                <h5 class="modal-title" v-if="!isInsert">Update Brand</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
             <form @submit.prevent="saveBrand" @keydown="form.onKeydown($event)">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12"><p><span class="required-field"><b>*</b></span> Required Field</p></div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="BrandCode"><span class="required-field">*</span> Brand Code</label>
                            <input type="text" class="form-control" id="BrandCode" name="BrandCode" v-model="form.BrandCode"  placeholder="Enter Brand Code"
                                                :class="{ 'is-invalid': form.errors.has('BrandCode') }">
                            <has-error :form="form" field="BrandCode"></has-error>
                        </div>
                        <div class="form-group">
                            <label for="BrandName"><span class="required-field">*</span> Brand Name</label>
                            <input type="text" class="form-control" id="BrandName" name="BrandName" v-model="form.BrandName"  placeholder="Enter Brand Name"
                                                :class="{ 'is-invalid': form.errors.has('BrandName') }">
                            <has-error :form="form" field="BrandName"></has-error>
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
        {label:'SL', name:'SL'},
        {label:'Model Code', name:'BrandCode'},
        {label:'Model Name', name:'BrandName'},
      ];
      columns.forEach((column) => {
          sortOrders[column.name] = 1;
      });
    return {
    base_url: '/waterpump/public',
      brands: [],
      columns:columns,
      sortKey: 'SL',
      sortOrders:sortOrders,
      perPage: ['20', '30', '40'],
      tableData:{
          draw:0,
          length:20,
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
            BrandCode: '',
            BrandName: ''
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
            this.brandList();
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
    brandList(url = this.base_url+'/api/brand/brandlist') {
        this.tableData.draw++;
      axios
        .get(url, { params: this.tableData }, { token: this.$store.state.token })
        .then((response) => {
            this.loader = false;
            if(this.tableData.draw == response.data.brands.draw){
                this.brands = response.data.brands.data.data;
                this.configPagination(response.data.brands.data);
            }

        })
        .catch((err) => {
            this.$toastr.error(err);
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
        this.brandList();
    },
    getIndex(array, key, value){
        return array.findIndex(i => i[key] == value)
    },
    createBrand() {
        this.isInsert = true;
        $('.modal').modal();
    },
    saveBrand() {
        this.form.post(this.base_url+'/api/brand/create')
        .then((response)=>{
                this.form.reset();
                $('.modal').modal('hide');
                this.$toastr.success('Brand created successfully');
                this.brandList();

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
                this.brandList();
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
