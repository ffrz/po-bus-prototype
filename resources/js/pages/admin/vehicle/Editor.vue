<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { create_options, scrollToFirstErrorField } from "@/helpers/utils";
import { useProductCategoryFilter } from "@/composables/useProductCategoryFilter";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Armada";

const form = useForm({
  id: page.props.data.id,
  category_id: page.props.data.category_id,
  name: page.props.data.name,
  plate_number: page.props.data.plate_number,
  capacity: page.props.data.capacity,
  type: page.props.data.type,
  active: !!page.props.data.active,
  notes: page.props.data.notes,
});

const types = [];
const categories = [];

const submit = () => handleSubmit({ form, url: route('admin.vehicle.save') });

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-md">
              <input type="hidden" name="id" v-model="form.id" />
              <q-input v-model.trim="form.name" label="Nama Armada" lazy-rules :error="!!form.errors.name"
                :disable="form.processing" :error-message="form.errors.name" :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]" />
              <q-input v-model.trim="form.plate_number" label="Plat Nomor" lazy-rules :error="!!form.errors.plate_number"
                :disable="form.processing" :error-message="form.errors.plate_number" :rules="[
                  (val) => (val && val.length > 0) || 'Plat Nomor harus diisi.',
                ]" />
              <q-input v-model.trim="form.capacity" label="Kapasitas" lazy-rules :error="!!form.errors.capacity"
                :disable="form.processing" :error-message="form.errors.capacity" :rules="[
                  (val) => (val && val.length > 0) || 'Kapasitas harus diisi.',
                ]" />
              <q-select v-model="form.type" label="Jenis"
                :options="types" map-options emit-value option-label="label"
                option-value="value" :error="!!form.errors.type" :disable="form.processing" />
              <q-select v-model="form.type" label="Kategori"
                :options="categories" map-options emit-value option-label="label"
                option-value="value" :error="!!form.errors.category_id" :disable="form.processing" />
              <div style="margin-left: -10px;">
                <q-checkbox class="full-width q-pl-none" v-model="form.active" :disable="form.processing"
                  label="Aktif" />
              </div>
              <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="1000" label="Catatan"
                lazy-rules :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes" />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
              <q-btn icon="cancel" label="Batal" :disable="form.processing"
                @click="router.get(route('admin.vehicle.index'))" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>

  </authenticated-layout>
</template>
