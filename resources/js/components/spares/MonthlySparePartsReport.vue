<template>
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h3>Monthly Spare Parts Report</h3>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><router-link :to="{ name: 'Home' }">Home</router-link></li>
              <li class="breadcrumb-item active">Monthly Spare Parts Report</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Title</h3>
        </div>
        <div class="card-body">Start creating your amazing application!</div>
        <div class="card-footer">Footer</div>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  data() {
    return {
      category: [],
    };
  },
  mounted() {
    this.allCategory();
  },
  methods: {
    allCategory() {
      axios
        .get("")
        .then((response) => {
          this.category = response.data.categoryList;
        })
        .catch((error) => {});
    },
  },
};
</script>
