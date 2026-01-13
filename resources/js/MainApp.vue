<template>
 <div class="wrapper h-100">
     <div v-if="this.$store.state.token">
  <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <router-link :to="{name: 'Home'}" class="brand-link">
                <img :src="adminLogo('/waterpump/public/img/aci-water-pump.jpg')" alt="WaterPump Logo"
                    class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">
                    <b v-if="this.$store.state.usertype!='CC'">Admin Panel</b>
                    <b v-else>Customer Care</b>
                </span>
            </router-link>

            <!-- Sidebar -->
            <div class="sidebar">

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-header">Main Navigation</li>
                        <li class="nav-item">
                            <router-link :to="{name : 'Home'}" class="nav-link">
                                <i class="fas fa-home"></i>
                                <p>Home</p>
                            </router-link>
                        </li>
                        <li class="nav-item" v-if="this.$store.state.usertype!='CC'">
                            <router-link :to="{name : 'Employee'}" class="nav-link">
                                <i class="fas fa-users"></i>
                                <p>Employee List</p>
                            </router-link>
                        </li>
                        <li class="nav-item" v-if="this.$store.state.usertype!='CC'">
                            <router-link :to="{name : 'TechnicianList'}" class="nav-link">
                                <i class="fas fa-users"></i>
                                <p>Technician List</p>
                            </router-link>
                        </li>
                        <!-- <li class="nav-item" v-if="this.$store.state.usertype!='CC'">
                            <router-link :to="{name : 'Brand'}" class="nav-link">
                                <i class="fas fa-arrow-circle-right"></i>
                                <p>Brand</p>
                            </router-link>
                        </li>
                        <li class="nav-item" v-if="this.$store.state.usertype!='CC'">
                            <router-link :to="{name : 'Product'}" class="nav-link">
                                <i class="fas fa-arrow-circle-right"></i>
                                <p>Product</p>
                            </router-link>
                        </li> -->
                        <li class="nav-item has-treeview" v-if="this.$store.state.usertype!='CC'">
                            <a href="#" class="nav-link">
                                <i class="fas fa-cogs"></i>
                                <p>
                                    Spare Parts
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview" style="display: none;">
                                <li class="nav-item">
                                    <router-link :to="{name : 'SparesStock'}" class="nav-link">
                                        <i class="fas fa-warehouse"></i>
                                        <p>Spare Parts Stock</p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link :to="{name : 'DailySparePartsReport'}" class="nav-link">
                                        <i class="fas fa-file"></i>
                                        <p>Daily Report</p>
                                    </router-link>
                                </li>
                                <!-- <li class="nav-item">
                                    <router-link :to="{name : 'MonthlySparePartsReport'}" class="nav-link">
                                        <i class="nav-icon fas fa-file"></i>
                                        <p>Monthly Report</p>
                                    </router-link>
                                </li> -->
                            </ul>
                        </li>
                        <li class="nav-item has-treeview" v-if="this.$store.state.usertype!='CC'">
                            <a href="#" class="nav-link">
                                <i class="fas fa-file"></i>
                                <p>
                                    Report
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview" style="display: none;">
                                <li class="nav-item" v-if="this.$store.state.usertype!='CC'">
                                    <router-link :to="{name : 'Report'}" class="nav-link">
                                        <i class="fas fa-file"></i>
                                        <p>Service Summary Report</p>
                                    </router-link>
                                </li>
                                <li class="nav-item" v-if="this.$store.state.usertype!='CC'">
                                  <router-link :to="{name : 'SelfReport'}" class="nav-link">
                                    <i class="fas fa-file"></i>
                                    <p>Self Service Report</p>
                                  </router-link>
                                </li>
                              <li class="nav-item" v-if="this.$store.state.usertype!='CC'">
                                <router-link :to="{name : 'OutSourceReport'}" class="nav-link">
                                  <i class="fas fa-file"></i>
                                  <p>Outsource Service Report</p>
                                </router-link>
                              </li>
                              <li class="nav-item" v-if="this.$store.state.usertype!='CC'">
                                <router-link :to="{name : 'MergedReport'}" class="nav-link">
                                  <i class="fas fa-file"></i>
                                  <p>Merged Service Report</p>
                                </router-link>
                              </li>
                              <li class="nav-item" v-if="this.$store.state.usertype!='CC'">
                                <router-link :to="{name : 'EngineerWiseCSI'}" class="nav-link">
                                  <i class="fas fa-file"></i>
                                  <p>Engineer Wise CSI</p>
                                </router-link>
                              </li>
<!--                                <li class="nav-item">-->
<!--                                    <router-link :to="{name : 'DetailReport'}" class="nav-link">-->
<!--                                        <i class="fas fa-file"></i>-->
<!--                                        <p>Service Detail Report</p>-->
<!--                                    </router-link>-->
<!--                                </li>-->
                            </ul>
                        </li>
                        <li class="nav-header">Account Settings</li>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="fas fa-cog"></i>
                                <p>
                                    Settings
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview" style="display: none;" v-if="this.$store.state.usertype!='CC'">
                                <li class="nav-item">
                                    <router-link :to="{name : 'ChangePassword'}" class="nav-link">
                                        <i class="fas fa-lock"></i>
                                        <p>Change Password</p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-header"></li>
                        <li class="nav-item" style="cursor:pointer">
                            <a @click.prevent="logout" class="nav-link">
                                <i class="fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
     </div>


    <router-view></router-view>

    <footer class="main-footer" v-if="this.$store.state.token">
        <strong>Copyright &copy; {{ new Date().getFullYear() }}
        <a href="https://acimotors-bd.com/products/agricultural-machineries/aci-pump" title="ACI Water Pump" target="_blank">ACI Motors.</a></strong>
        Developed by MIS
        <div class="float-right d-none d-sm-inline-block">
        </div>
    </footer>

    <aside class="control-sidebar control-sidebar-dark">
    </aside>
    </div>

</template>

<script>
    export default {
        data() {
            return {}
        },

        mounted() {

        },

        methods: {
            adminLogo(img){
               return window.location.origin + img
            },
            logout() {
                // console.log('hlw');
                const accessToken = this.$store.state.token;
                if (accessToken) {
                    // console.log(accessToken);
                    axios.defaults.headers.common['Authorization'] = 'Bearer ' + accessToken
                    axios
                    .post("/waterpump/public/api/auth/logout", { token: accessToken })
                    .then((res) => {
                        this.$store.commit("clearToken");
                        this.$router.push({name: 'Login'});
                        this.$toastr.success('Successfully Logged Out');
                })
                .catch((err) => {
                    this.$store.commit("clearToken");
                    this.$router.push({name: 'Login'});
                    this.$toastr.success('Please login again');
                    console.log(err);
                });
                }else{
                    this.$store.commit("clearToken");
                    this.$router.push({name: 'Login'});
                    this.$toastr.success('Please login again');
                }
            }
        }
    }
</script>

<style scoped>
.nav-item a.active{
    color: red;
}
</style>
