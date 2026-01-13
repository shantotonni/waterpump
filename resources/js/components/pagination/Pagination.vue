<style lang="scss" scoped>
.pagination{
    justify-content: flex-end !important;
    .page-stats{
        align-items: center;
        margin-right: 15px;
        margin-top: 7px;
    }
    i{
        color: #a01f1f !important;
    }
}
.page-link{
    cursor: pointer;
}
</style>
<template>
<nav class="pagination d-flex justify-content-center">
  <ul class="pagination">
    <li class="page-item page-stats">{{pagination.from}} - {{pagination.to}} of {{pagination.total}}</li>
    <li class="page-item" v-if="pagination.prevPageUrl"><a class="page-link" @click="$emit('prev');">Prev</a></li>
    <li class="page-item" v-else><a class="page-link" :disabled="true">Prev</a></li>
    <li class="page-item" v-if="pagination.nextPageUrl"><a class="page-link" @click="$emit('next');">Next</a></li>
    <li class="page-item" v-else><a class="page-link" :disabled="true">Next</a></li>
  </ul>
</nav>
</template>

<script>

export default {
    props:['pagination']
}
</script>

