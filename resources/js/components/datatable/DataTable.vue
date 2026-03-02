<style scoped>
.arrow {
  border: solid black;
  border-width: 0 1px 1px 0;
  display: inline-block;
  padding: 2px;
}
.up {
  transform: rotate(-135deg);
  -webkit-transform: rotate(-135deg);
}
.down {
  transform: rotate(45deg);
  -webkit-transform: rotate(45deg);
}
.tableFixHead          { overflow-y: auto; height: 500px; }
.tableFixHead thead th { position: sticky; top: 0; }

/* Just common table stuff. Really. */
table  { border-collapse: collapse; width: 100%; font-size: 10pt; }
th     { background:#eee; }
</style>

<template>

<div class="table-responsive tableFixHead">
    <table class="table table-bordered table-sm text-center small">
        <thead>
            <tr>
                <th v-for="column in columns" :key="column.name" @click="$emit('sort', column.name)"
                    :class="sortKey==column.name ? (sortOrders[column.name] > 0 ? 'sorting_asc':'sorting_desc'):'sorting'"
                    :style="'width:'+column.width+';'+'cursor:pointer;'" class="sorting th-sm">
                        {{ column.label }}

                </th>
            </tr>
        </thead>
    <slot></slot>
</table>
</div>

</template>

<script>

export default {
    props: ['columns', 'sortKey', 'sortOrders'],
    mounted() {
    }
}
</script>

