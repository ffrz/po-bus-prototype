<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";

const page = usePage();
const title = `Rincian Armada #${page.props.data.code}`;
const $q = useQuasar();
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="router.get(route('admin.vehicle.index'))"
        />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="edit"
          dense
          color="primary"
          v-if="$can('admin.vehicle.edit')"
          @click="
            router.get(route('admin.vehicle.edit', { id: page.props.data.id }))
          "
        />
      </div>
    </template>

    <div class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 140px">Kode</td>
                    <td style="width: 1px">:</td>
                    <td>{{ page.props.data.code }}</td>
                  </tr>
                  <tr>
                    <td>Deskripsi</td>
                    <td>:</td>
                    <td>{{ page.props.data.description || "-" }}</td>
                  </tr>
                  <tr>
                    <td>Jenis</td>
                    <td>:</td>
                    <td>
                      {{
                        $CONSTANTS.VEHICLE_TYPES[page.props.data.type] || "-"
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Plat Nomor</td>
                    <td>:</td>
                    <td>{{ page.props.data.plate_number || "-" }}</td>
                  </tr>
                  <tr>
                    <td>Kapasitas</td>
                    <td>:</td>
                    <td>{{ page.props.data.capacity }} penumpang</td>
                  </tr>
                  <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>
                      {{
                        $CONSTANTS.VEHICLE_STATUSES[page.props.data.status] ||
                        "-"
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Merk</td>
                    <td>:</td>
                    <td>{{ page.props.data.brand || "-" }}</td>
                  </tr>
                  <tr>
                    <td>Model</td>
                    <td>:</td>
                    <td>{{ page.props.data.model || "-" }}</td>
                  </tr>
                  <tr>
                    <td>Tahun</td>
                    <td>:</td>
                    <td>{{ page.props.data.year || "-" }}</td>
                  </tr>
                  <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ page.props.data.notes || "-" }}</td>
                  </tr>

                  <tr v-if="page.props.data.created_at">
                    <td>Dibuat oleh</td>
                    <td>:</td>
                    <td>
                      <template v-if="page.props.data.created_by">
                        <i-link
                          :href="
                            route('admin.user.detail', {
                              id: page.props.data.created_by.id,
                            })
                          "
                        >
                          {{ page.props.data.created_by.username }} -
                          {{ page.props.data.created_by.name }}
                        </i-link>
                        -
                      </template>
                      {{
                        $dayjs(page.props.data.created_at).format(
                          "dddd, D MMMM YYYY [pukul] HH:mm:ss"
                        )
                      }}
                    </td>
                  </tr>
                  <tr v-if="page.props.data.updated_at">
                    <td>Diperbarui oleh</td>
                    <td>:</td>
                    <td>
                      <template v-if="page.props.data.updated_by">
                        <i-link
                          :href="
                            route('admin.user.detail', {
                              id: page.props.data.updated_by.id,
                            })
                          "
                        >
                          {{ page.props.data.updated_by.username }} -
                          {{ page.props.data.updated_by.name }}
                        </i-link>
                        -
                      </template>
                      {{
                        $dayjs(page.props.data.updated_at).format(
                          "dddd, D MMMM YYYY [pukul] HH:mm:ss"
                        )
                      }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
