<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { usePageStorage } from "@/composables/usePageStorage";
import { vehicleStatusOptions } from "@/helpers/options";

const page = usePage();
const storage = usePageStorage("vehicle");

const title = "Armada";
const $q = useQuasar();

const showFilter = ref(storage.get("show-filter", false));
const rows = ref([]);
const loading = ref(true);

// Filter default
const filter = reactive(
  storage.get("filter", {
    status: "all",
    search: "",
    ...getQueryParams(),
  })
);

// Pagination default
const pagination = ref(
  storage.get("pagination", {
    page: 1,
    rowsPerPage: 10,
    rowsNumber: 10,
    sortBy: "code",
    descending: false,
  })
);

// Opsi status
const statuses = [{ value: "all", label: "Semua" }, ...vehicleStatusOptions()];

// Kolom tabel
const columns = [
  {
    name: "code",
    label: "Kode",
    field: "code",
    align: "left",
    sortable: true,
  },
  {
    name: "description",
    label: "Deskripsi",
    field: "description",
    align: "left",
    sortable: true,
  },
  { name: "status", label: "Status", field: "status", align: "left" },
  { name: "action", align: "right" },
];

onMounted(() => {
  fetchItems();
});

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.vehicle.data"),
    loading,
  });
};

// Hapus data
const deleteItem = (row) =>
  handleDelete({
    message: `Hapus kendaraan ${row.name}?`,
    url: route("admin.vehicle.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

// Filter kolom untuk mobile
const computedColumns = computed(() =>
  $q.screen.gt.sm
    ? columns
    : columns.filter((col) => ["code", "action"].includes(col.name))
);

// Watchers
watch(filter, () => storage.set("filter", filter), { deep: true });
watch(showFilter, () => storage.set("show-filter", showFilter.value));
watch(pagination, () => storage.set("pagination", pagination.value));
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>

    <template #right-button>
      <q-btn
        icon="add"
        dense
        color="primary"
        @click="router.get(route('admin.vehicle.add'))"
        v-if="$can('admin.vehicle.add')"
      />
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        @click="showFilter = !showFilter"
      />
      <q-btn
        v-if="$can('admin.vehicle.export')"
        icon="file_export"
        dense
        class="q-ml-sm"
        color="grey"
      >
        <q-menu
          anchor="bottom right"
          self="top right"
          transition-show="scale"
          transition-hide="scale"
        >
          <q-list style="width: 200px">
            <q-item
              clickable
              v-ripple
              v-close-popup
              :href="route('admin.vehicle.export', { format: 'pdf' })"
            >
              <q-item-section avatar>
                <q-icon name="picture_as_pdf" color="red-9" />
              </q-item-section>
              <q-item-section>Export PDF</q-item-section>
            </q-item>
            <q-item
              clickable
              v-ripple
              v-close-popup
              :href="route('admin.vehicle.export', { format: 'excel' })"
            >
              <q-item-section avatar>
                <q-icon name="csv" color="green-9" />
              </q-item-section>
              <q-item-section>Export Excel</q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-btn>
    </template>

    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            v-model="filter.status"
            class="col-xs-12 col-sm-2"
            :options="statuses"
            label="Status"
            dense
            outlined
            map-options
            emit-value
            @update:model-value="fetchItems"
          />

          <q-input
            class="col"
            outlined
            dense
            debounce="300"
            v-model="filter.search"
            placeholder="Cari kendaraan"
            clearable
            @update:model-value="fetchItems"
          >
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </q-toolbar>
    </template>

    <div class="q-pa-sm">
      <q-table
        flat
        bordered
        square
        color="primary"
        row-key="id"
        virtual-scroll
        v-model:pagination="pagination"
        :filter="filter.search"
        :loading="loading"
        :columns="computedColumns"
        :rows="rows"
        :rows-per-page-options="[10, 25, 50]"
        @request="fetchItems"
        binary-state-sort
      >
        <template v-slot:loading>
          <q-inner-loading showing color="primary" />
        </template>

        <template v-slot:no-data="{ message, filter }">
          <div class="full-width row flex-center text-grey-8 q-gutter-sm">
            <span
              >{{ message }}
              {{ filter ? " dengan pencarian " + filter : "" }}</span
            >
          </div>
        </template>

        <template v-slot:body="props">
          <q-tr
            :props="props"
            :class="props.row.status !== 'active' ? 'inactive' : 'active'"
            class="cursor-pointer"
            @click="router.get(route('admin.vehicle.detail', props.row.id))"
          >
            <q-td key="code" :props="props" class="wrap-column">
              {{ props.row.code }}
              <div v-if="!$q.screen.gt.sm" class="text-grey-8">
                <div>{{ props.row.description }}</div>
                <q-badge class="q-mr-xs">{{
                  $CONSTANTS.VEHICLE_TYPES?.[props.row.type] || "–"
                }}</q-badge>
                <q-badge>{{
                  $CONSTANTS.VEHICLE_STATUSES?.[props.row.status] || "–"
                }}</q-badge>
              </div>
            </q-td>
            <q-td key="description" :props="props" class="wrap-column">
              {{ props.row.description }}
            </q-td>
            <q-td key="status" :props="props" class="wrap-column">
              {{ $CONSTANTS.VEHICLE_STATUSES?.[props.row.status] || "–" }}
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn
                  icon="more_vert"
                  dense
                  flat
                  @click.stop
                  style="height: 40px; width: 30px"
                  v-if="
                    $can('admin.vehicle.duplicate') ||
                    $can('admin.vehicle.edit') ||
                    $can('admin.vehicle.delete')
                  "
                >
                  <q-menu anchor="bottom right" self="top right">
                    <q-list style="width: 200px">
                      <q-item
                        v-if="$can('admin.vehicle.duplicate')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('admin.vehicle.duplicate', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar
                          ><q-icon name="file_copy"
                        /></q-item-section>
                        <q-item-section>Duplikat</q-item-section>
                      </q-item>
                      <q-item
                        v-if="$can('admin.vehicle.edit')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(route('admin.vehicle.edit', props.row.id))
                        "
                      >
                        <q-item-section avatar
                          ><q-icon name="edit"
                        /></q-item-section>
                        <q-item-section>Edit</q-item-section>
                      </q-item>
                      <q-item
                        v-if="$can('admin.vehicle.delete')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="deleteItem(props.row)"
                      >
                        <q-item-section avatar
                          ><q-icon name="delete_forever"
                        /></q-item-section>
                        <q-item-section>Hapus</q-item-section>
                      </q-item>
                    </q-list>
                  </q-menu>
                </q-btn>
              </div>
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </authenticated-layout>
</template>
