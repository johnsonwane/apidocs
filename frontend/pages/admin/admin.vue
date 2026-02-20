<template>
  <view class="wrapper">
    <view class="card login" v-if="!token">
      <view class="title">管理员登录</view>
      <input v-model="loginForm.username" class="input" placeholder="用户名" />
      <input v-model="loginForm.password" password class="input" placeholder="密码" />
      <button class="btn" @click="login">登录</button>
      <text class="tip">测试账号：admin / admin123</text>
    </view>

    <view class="card" v-else>
      <view class="toolbar">
        <view class="title">分类管理</view>
        <button size="mini" @click="createCategory">新增分类</button>
      </view>
      <view v-for="item in categories" :key="item.id" class="row">
        <text>{{ item.name }}</text>
        <view>
          <button size="mini" @click="editCategory(item)">编辑</button>
          <button size="mini" type="warn" @click="removeCategory(item.id)">删除</button>
        </view>
      </view>
    </view>

    <view class="card" v-if="token">
      <view class="toolbar">
        <view class="title">API 管理</view>
        <button size="mini" @click="startCreateApi">新增 API</button>
      </view>
      <view v-for="item in apis" :key="item.id" class="row column">
        <text>{{ item.method }} {{ item.path }} - {{ item.name }}</text>
        <view>
          <button size="mini" @click="startEditApi(item)">编辑</button>
          <button size="mini" type="warn" @click="removeApi(item.id)">删除</button>
        </view>
      </view>
    </view>

    <view class="card" v-if="token">
      <view class="title">{{ editingApiId ? '编辑 API' : '新增 API' }}</view>
      <input class="input" v-model="apiForm.name" placeholder="API 名称" />
      <picker class="picker" :range="methodOptions" @change="onMethodChange">
        <view class="input picker-value">请求方法：{{ apiForm.method }}</view>
      </picker>
      <input class="input" v-model="apiForm.path" placeholder="路径 /v1/xxx" />
      <input class="input" v-model="apiForm.category_id" placeholder="分类ID" type="number" />
      <textarea class="textarea" v-model="apiForm.description" placeholder="接口描述" />

      <view class="sub-title">请求参数表（JSON数组，必须含 name/type/required/description）</view>
      <text class="tip">示例：[{"name":"id","type":"number","required":true,"description":"用户ID"}]</text>
      <textarea class="textarea" v-model="apiForm.request_params" placeholder='[{"name":"id","type":"number","required":true,"description":"用户ID"}]' />

      <view class="sub-title">返回字段表（JSON数组，必须含 name/type/description）</view>
      <text class="tip">示例：[{"name":"code","type":"number","description":"状态码"}]</text>
      <textarea class="textarea" v-model="apiForm.response_fields" placeholder='[{"name":"code","type":"number","description":"状态码"}]' />

      <view class="sub-title">请求示例 JSON</view>
      <textarea class="textarea" v-model="apiForm.request_example" placeholder='{"id":1}' />

      <view class="sub-title">响应示例 JSON</view>
      <textarea class="textarea" v-model="apiForm.response_example" placeholder='{"code":0}' />

      <input class="input" v-model="apiForm.test_url" placeholder="测试URL(可选)" />
      <view class="form-actions">
        <button size="mini" class="btn" @click="submitApiForm">{{ editingApiId ? '保存修改' : '确认新增' }}</button>
        <button size="mini" @click="resetApiForm">清空</button>
      </view>
    </view>
  </view>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { apiClient } from '../../common/api'

const token = ref(uni.getStorageSync('admin_token') || '')
const categories = ref([])
const apis = ref([])
const loginForm = ref({ username: 'admin', password: 'admin123' })
const editingApiId = ref(0)
const apiForm = ref(getEmptyApiForm())
const methodOptions = ['GET', 'POST', 'PUT', 'DELETE']

function getEmptyApiForm() {
  return {
    name: '',
    method: 'GET',
    path: '',
    category_id: '',
    description: '',
    request_params: '[]',
    response_fields: '[]',
    request_example: '{}',
    response_example: '{}',
    test_url: ''
  }
}

async function login() {
  const res = await apiClient.login(loginForm.value)
  token.value = res.data.token
  uni.setStorageSync('admin_token', token.value)
  await initData()
}

async function initData() {
  const [catRes, apiRes] = await Promise.all([apiClient.getCategories(), apiClient.getApis()])
  categories.value = catRes.data
  apis.value = apiRes.data
}

async function createCategory() {
  const [ok, value] = await prompt('输入分类名称')
  if (!ok || !value) return
  await apiClient.createCategory({ name: value }, token.value)
  await initData()
}

async function editCategory(item) {
  const [ok, value] = await prompt('修改分类名称', item.name)
  if (!ok || !value) return
  await apiClient.updateCategory(item.id, { name: value }, token.value)
  await initData()
}

async function removeCategory(id) {
  await apiClient.deleteCategory(id, token.value)
  await initData()
}

function startCreateApi() {
  editingApiId.value = 0
  apiForm.value = getEmptyApiForm()
}

async function startEditApi(item) {
  const detail = await apiClient.getApiDetail(item.id)
  editingApiId.value = item.id
  apiForm.value = {
    name: detail.data.name || '',
    method: detail.data.method || 'GET',
    path: detail.data.path || '',
    category_id: String(detail.data.category_id || ''),
    description: detail.data.description || '',
    request_params: detail.data.request_params || '[]',
    response_fields: detail.data.response_fields || '[]',
    request_example: detail.data.request_example || '{}',
    response_example: detail.data.response_example || '{}',
    test_url: detail.data.test_url || ''
  }
}

function onMethodChange(e) {
  const idx = Number(e.detail.value)
  apiForm.value.method = methodOptions[idx] || 'GET'
}

function validateRequestParams(rows) {
  if (!Array.isArray(rows)) return '请求参数表必须是 JSON 数组'
  for (let i = 0; i < rows.length; i++) {
    const row = rows[i]
    if (!row || typeof row !== 'object') return `请求参数第 ${i + 1} 行必须是对象`
    const requiredKeys = ['name', 'type', 'required', 'description']
    for (const key of requiredKeys) {
      if (!(key in row)) return `请求参数第 ${i + 1} 行缺少字段：${key}`
    }
    if (typeof row.name !== 'string' || !row.name.trim()) return `请求参数第 ${i + 1} 行 name 必须是非空字符串`
    if (typeof row.type !== 'string' || !row.type.trim()) return `请求参数第 ${i + 1} 行 type 必须是非空字符串`
    if (typeof row.required !== 'boolean') return `请求参数第 ${i + 1} 行 required 必须是布尔值 true/false`
    if (typeof row.description !== 'string') return `请求参数第 ${i + 1} 行 description 必须是字符串`
  }
  return ''
}

function validateResponseFields(rows) {
  if (!Array.isArray(rows)) return '返回字段表必须是 JSON 数组'
  for (let i = 0; i < rows.length; i++) {
    const row = rows[i]
    if (!row || typeof row !== 'object') return `返回字段第 ${i + 1} 行必须是对象`
    const requiredKeys = ['name', 'type', 'description']
    for (const key of requiredKeys) {
      if (!(key in row)) return `返回字段第 ${i + 1} 行缺少字段：${key}`
    }
    if (typeof row.name !== 'string' || !row.name.trim()) return `返回字段第 ${i + 1} 行 name 必须是非空字符串`
    if (typeof row.type !== 'string' || !row.type.trim()) return `返回字段第 ${i + 1} 行 type 必须是非空字符串`
    if (typeof row.description !== 'string') return `返回字段第 ${i + 1} 行 description 必须是字符串`
  }
  return ''
}

async function submitApiForm() {
  const payload = {
    ...apiForm.value,
    category_id: Number(apiForm.value.category_id)
  }

  if (!methodOptions.includes((payload.method || '').toUpperCase())) {
    uni.showToast({ title: 'method 仅支持 GET/POST/PUT/DELETE', icon: 'none' })
    return
  }
  payload.method = payload.method.toUpperCase()

  let requestRows = []
  let responseRows = []
  try {
    requestRows = JSON.parse(payload.request_params || '[]')
    responseRows = JSON.parse(payload.response_fields || '[]')
    JSON.parse(payload.request_example || '{}')
    JSON.parse(payload.response_example || '{}')
  } catch (e) {
    uni.showToast({ title: 'JSON 格式不正确', icon: 'none' })
    return
  }

  const reqError = validateRequestParams(requestRows)
  if (reqError) {
    uni.showToast({ title: reqError, icon: 'none' })
    return
  }

  const respError = validateResponseFields(responseRows)
  if (respError) {
    uni.showToast({ title: respError, icon: 'none' })
    return
  }

  try {
    if (editingApiId.value) {
      await apiClient.updateApi(editingApiId.value, payload, token.value)
    } else {
      await apiClient.createApi(payload, token.value)
    }
    await initData()
    resetApiForm()
  } catch (e) {
    if (e && e._statusCode === 401) {
      uni.removeStorageSync('admin_token')
      token.value = ''
      uni.showToast({ title: '登录已失效，请重新登录', icon: 'none' })
      return
    }
    uni.showToast({ title: e?.message || '保存失败', icon: 'none' })
  }
}

function resetApiForm() {
  editingApiId.value = 0
  apiForm.value = getEmptyApiForm()
}

async function removeApi(id) {
  await apiClient.deleteApi(id, token.value)
  await initData()
}

function prompt(title, defaultValue = '') {
  return new Promise((resolve) => {
    uni.showModal({
      title,
      editable: true,
      placeholderText: '请输入内容',
      content: defaultValue,
      success: (res) => resolve([res.confirm, res.content])
    })
  })
}

onMounted(() => {
  if (token.value) initData()
})
</script>

<style scoped>
.wrapper { padding: 16px; display: grid; gap: 16px; }
.card { background: #fff; border-radius: 8px; padding: 16px; }
.login { max-width: 420px; }
.title { font-size: 18px; font-weight: 600; margin-bottom: 12px; }
.sub-title { font-size: 14px; margin: 6px 0; color: #4e5969; }
.input { border: 1px solid #dcdfe6; border-radius: 6px; padding: 10px; margin-bottom: 10px; }
.picker { margin-bottom: 10px; }
.picker-value { color: #1f2329; }
.textarea { width: 100%; min-height: 88px; border: 1px solid #dcdfe6; border-radius: 6px; padding: 10px; margin-bottom: 10px; }
.btn { background: #0066cc; color: #fff; }
.tip { color: #86909c; font-size: 12px; }
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.row { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #f5f5f5; }
.column { flex-direction: column; align-items: flex-start; gap: 8px; }
.form-actions { display: flex; gap: 8px; }
</style>
