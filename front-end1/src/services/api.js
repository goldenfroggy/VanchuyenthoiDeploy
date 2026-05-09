const BASE_URL = import.meta.env.VITE_API_URL;

// GET
export async function getData(endpoint) {
  const res = await fetch(`${BASE_URL}${endpoint}`);
  return await res.json();
}

// POST
export async function postData(endpoint, data) {
  const res = await fetch(`${BASE_URL}${endpoint}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  });

  return await res.json();
}