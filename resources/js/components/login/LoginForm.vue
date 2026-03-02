<template>
<div class="container h-100 w-75">
    <div class="row h-100 justify-content-center align-items-center">
        <div class="row w-100 p-5 justify-content-center align-items-center border border-primary">
            <div class="col-sm">
                <div class="mx-auto h-100 d-flex justify-content-center">
                    <img class="img-responsive rounded-circle" :src="'./img/aci-water-pump.jpg'"
                        alt="Water Pump">
                </div>
          </div>
            <div class="col-sm">
                <div class="login-box mx-auto">
                    <div class="card h-100 card-outline card-primary">
                        <div class="card-header text-center">
                            Sign in
                        </div>
                        <div class="card-body">

                            <form @submit.prevent="login" @keydown="form.onKeydown($event)">
                                <div class="form-group">
                                    <label for="userid">User ID</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" v-model="form.staffid" name="staffid" placeholder="Staff ID"
                                                            :class="{ 'is-invalid': form.errors.has('staffid') }">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-envelope"></span>
                                            </div>
                                        </div>
                                        <has-error :form="form" field="staffid"></has-error>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password</label>
                                        <div class="input-group mb-3">
                                        <input type="password" class="form-control" v-model="form.password" name="password" placeholder="Password"
                                                                :class="{ 'is-invalid': form.errors.has('password') }">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-lock"></span>
                                            </div>
                                        </div>
                                        <has-error :form="form" field="password"></has-error>
                                    </div>
                                </div>
                                <div class="p-2"></div>
                                <div class="row">
                                    <div class="col-6"></div>
                                    <div class="col-6">
                                        <button :disabled="form.busy" type="submit" class="btn btn-primary btn-block"><span class="fas fa-sign-in-alt"></span> Sign In</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
          </div>
        </div>
    </div>
  </div>

</template>

<script>
import axios from "axios";
export default {
    name:'Login',
  data() {
      return {
          form: new Form({
            staffid: '',
            password: ''
         }),
      }
  },
  mounted() {
      if(localStorage.getItem('auth') !== null){
          this.isAuthenticate = true;
          this.$router.push({name : 'Home'});
      }
  },
  methods: {
    login() {
        this.form.post('/waterpump/public/api/auth/dashboardlogin')
        .then((response)=>{
                this.$store.commit("setToken", response.data.token);
                this.$store.commit("setUserId", response.data.userinfo.StaffID);
                this.$store.commit("setUserType", response.data.userinfo.UserType);
                this.$toastr.success('Successfully Logged in');
                // this.$router.push({name : 'Home'});
                window.location.href = 'home';
        }).catch((error)=>{
                this.$store.commit("clearToken");
                this.$store.commit("clearUserId");
                this.$store.commit("clearUserType");
                this.$toastr.error('These credentials does not match our records');
        })
    },
  }
};
</script>
