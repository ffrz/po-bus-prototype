<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { vehicleTypeOptions, vehicleStatusOptions } from "@/helpers/options";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Armada";

const form = useForm({
  id: page.props.data.id,
  code: page.props.data.code ?? "",
  description: page.props.data.description ?? "",
  type: page.props.data.type ?? "",
  plate_number: page.props.data.plate_number ?? "",
  capacity: page.props.data.capacity ?? 0,
  status: page.props.data.status ?? "",
  brand: page.props.data.brand ?? "",
  model: page.props.data.model ?? "",
  year: page.props.data.year ?? "",
  notes: page.props.data.notes ?? "",
});

const types = vehicleTypeOptions();
const statuses = vehicleStatusOptions();

const submit = () => handleSubmit({ form, url: route("admin.vehicle.save") });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-md">
              <input type="hidden" name="id" v-model="form.id" />

              <q-input
                v-model.trim="form.code"
                label="Kode Armada"
                maxlength="20"
                lazy-rules
                :error="!!form.errors.code"
                :error-message="form.errors.code"
                :disable="form.processing"
                :rules="[(val) => !!val || 'Kode harus diisi.']"
              />

              <q-input
                v-model.trim="form.description"
                label="Deskripsi"
                maxlength="100"
                lazy-rules
                :error="!!form.errors.description"
                :error-message="form.errors.description"
                :disable="form.processing"
                :rules="[(val) => !!val || 'Deskripsi harus diisi.']"
              />

              <q-select
                v-model="form.type"
                label="Jenis Armada"
                :options="types"
                map-options
                emit-value
                option-label="label"
                option-value="value"
                :error="!!form.errors.type"
                :error-message="form.errors.type"
                :disable="form.processing"
              />

              <q-select
                v-model="form.status"
                label="Status"
                :options="statuses"
                map-options
                emit-value
                option-label="label"
                option-value="value"
                :error="!!form.errors.status"
                :error-message="form.errors.status"
                :disable="form.processing"
              />
              <q-input
                v-model.trim="form.plate_number"
                label="Plat Nomor"
                maxlength="20"
                lazy-rules
                :error="!!form.errors.plate_number"
                :error-message="form.errors.plate_number"
                :disable="form.processing"
                :rules="[(val) => !!val || 'Plat nomor harus diisi.']"
              />

              <q-input
                v-model.number="form.capacity"
                label="Kapasitas Kursi"
                type="number"
                min="0"
                :error="!!form.errors.capacity"
                :error-message="form.errors.capacity"
                :disable="form.processing"
              />

              <q-input
                v-model.trim="form.brand"
                label="Merek"
                maxlength="40"
                :error="!!form.errors.brand"
                :error-message="form.errors.brand"
                :disable="form.processing"
              />

              <q-input
                v-model.trim="form.model"
                label="Model"
                maxlength="40"
                :error="!!form.errors.model"
                :error-message="form.errors.model"
                :disable="form.processing"
              />

              <q-input
                v-model.number="form.year"
                label="Tahun"
                type="number"
                min="1950"
                max="2100"
                :error="!!form.errors.year"
                :error-message="form.errors.year"
                :disable="form.processing"
              />

              <q-input
                v-model.trim="form.notes"
                label="Catatan"
                type="textarea"
                autogrow
                maxlength="1000"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                :disable="form.processing"
              />
            </q-card-section>

            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="save"
                type="submit"
                label="Simpan"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                flat
                :disable="form.processing"
                @click="router.get(route('admin.vehicle.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
