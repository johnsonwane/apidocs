// 线上同域走 /api；本地 HBuilderX H5 调试通过 vite proxy 转发到线上
const BASE_URL = '/api'

function request(url, method = 'GET', data = {}, token = '') {
  return new Promise((resolve, reject) => {
    uni.request({
      url: `${BASE_URL}${url}`,
      method,
      data,
      header: {
        'Content-Type': 'application/json',
        Authorization: token ? `Bearer ${token}` : ''
      },
      success: (res) => {
        if (res.statusCode >= 200 && res.statusCode < 300) {
          resolve(res.data)
        } else {
          reject({ ...(res.data || {}), _statusCode: res.statusCode })
        }
      },
      fail: reject
    })
  })
}

export const apiClient = {
  getCategories: () => request('/categories/index.php'),
  createCategory: (payload, token) => request('/categories/index.php', 'POST', payload, token),
  updateCategory: (id, payload, token) => request('/categories/update.php', 'POST', { id, ...payload }, token),
  deleteCategory: (id, token) => request('/categories/delete.php', 'POST', { id }, token),

  getApis: (categoryId = '') => request(`/apis/index.php${categoryId ? `?category_id=${categoryId}` : ''}`),
  getApiDetail: (id) => request(`/apis/detail.php?id=${id}`),
  createApi: (payload, token) => request('/apis/create.php', 'POST', payload, token),
  updateApi: (id, payload, token) => request('/apis/update.php', 'POST', { id, ...payload }, token),
  deleteApi: (id, token) => request('/apis/delete.php', 'POST', { id }, token),

  login: (payload) => request('/auth/login.php', 'POST', payload),
  runTest: (id, payload) => request('/apis/test.php', 'POST', { id, payload })
}
