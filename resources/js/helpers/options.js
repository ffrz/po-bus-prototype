export function vehicleTypeOptions() {
  const types = window.CONSTANTS?.VEHICLE_TYPES || {};

  return Object.entries(types).map(([key, label]) => ({
    value: key,
    label: label,
  }));
}

export function vehicleStatusOptions() {
  const items = window.CONSTANTS?.VEHICLE_STATUSES || {};

  return Object.entries(items).map(([key, label]) => ({
    value: key,
    label: label,
  }));
}

export function categoryOptions(categories) {
  return categories.map(item => ({
    value: item.id,
    label: item.name
  }));
}
