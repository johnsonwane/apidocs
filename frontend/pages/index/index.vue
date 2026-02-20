<template>
  <view class="layout">
    <view class="sidebar card">
      <view class="panel-title">分类</view>
      <view
        v-for="item in categories"
        :key="item.id"
        class="category-item"
        :class="{ active: item.id === selectedCategoryId }"
        @click="selectCategory(item.id)"
      >
        {{ item.name }}
      </view>
    </view>

    <view class="main card">
      <view class="header-row">
        <view>
          <view class="panel-title">API 列表</view>
          <view class="hint">无需登录即可浏览文档与在线测试</view>
        </view>
        <navigator url="/pages/admin/admin" class="admin-link">进入管理后台</navigator>
      </view>

      <view class="api-list">
        <view
          v-for="item in apis"
          :key="item.id"
          class="api-item"
          :class="{ active: item.id === selectedApiId }"
          @click="selectApi(item.id)"
        >
          <text class="method" :class="item.method.toLowerCase()">{{ item.method }}</text>
          <text class="title">{{ item.name }}</text>
          <text class="path">{{ item.path }}</text>
        </view>
      </view>

      <view v-if="apiDetail" class="detail">
        <view class="section-title">{{ apiDetail.name }}</view>
        <view class="desc">{{ apiDetail.description || '暂无说明' }}</view>

        <view class="meta-grid">
          <view><text class="label">方法</text>{{ apiDetail.method }}</view>
          <view><text class="label">路径</text>{{ apiDetail.path }}</view>
        </view>

        <view class="section-title">请求参数表</view>
        <view class="table-wrap">
          <view class="table-row head">
            <text>参数名</text><text>类型</text><text>必填</text><text>说明</text>
          </view>
          <view class="table-row" v-for="(row, idx) in requestParams" :key="`req-${idx}`">
            <text>{{ row.name || '-' }}</text>
            <text>{{ row.type || '-' }}</text>
            <text>{{ row.required ? '是' : '否' }}</text>
            <text>{{ row.description || '-' }}</text>
          </view>
          <view class="table-row" v-if="!requestParams.length">
            <text>-</text><text>-</text><text>-</text><text>无参数</text>
          </view>
        </view>

        <view class="section-title">返回字段表</view>
        <view class="table-wrap">
          <view class="table-row head">
            <text>字段名</text><text>类型</text><text>说明</text>
          </view>
          <view class="table-row" v-for="(row, idx) in responseFields" :key="`resp-${idx}`">
            <text>{{ row.name || '-' }}</text>
            <text>{{ row.type || '-' }}</text>
            <text>{{ row.description || '-' }}</text>
          </view>
          <view class="table-row" v-if="!responseFields.length">
            <text>-</text><text>-</text><text>无字段定义</text>
          </view>
        </view>

        <view class="section-title">请求示例</view>
        <textarea class="code-area" disabled :value="apiDetail.request_example || '{}'" />

        <view class="section-title">响应示例</view>
        <textarea class="code-area" disabled :value="apiDetail.response_example || '{}'" />

        <view class="section-title">在线测试</view>
        <textarea class="code-area" v-model="testPayload" placeholder="输入 JSON 参数，如：{\"id\":1}" />
        <button class="btn" @click="runTest">执行测试</button>
        <textarea class="code-area" disabled :value="testResult" placeholder="测试结果将展示在这里" />
      </view>
    </view>
  </view>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { apiClient } from '../../common/api'

const categories = ref([])
const selectedCategoryId = ref(0)
const apis = ref([])
const selectedApiId = ref(0)
const apiDetail = ref(null)
const requestParams = ref([])
const responseFields = ref([])
const testPayload = ref('{}')
const testResult = ref('')

function parseArrayJson(raw) {
  try {
    const parsed = JSON.parse(raw || '[]')
    return Array.isArray(parsed) ? parsed : []
  } catch (e) {
    return []
  }
}

async function loadCategories() {
  const res = await apiClient.getCategories()
  categories.value = [{ id: 0, name: '全部' }, ...res.data]
}

async function loadApis() {
  const res = await apiClient.getApis(selectedCategoryId.value)
  apis.value = res.data
  if (apis.value.length) {
    selectApi(apis.value[0].id)
  } else {
    selectedApiId.value = 0
    apiDetail.value = null
    requestParams.value = []
    responseFields.value = []
  }
}

async function selectCategory(id) {
  selectedCategoryId.value = id
  await loadApis()
}

async function selectApi(id) {
  selectedApiId.value = id
  const res = await apiClient.getApiDetail(id)
  apiDetail.value = res.data
  requestParams.value = parseArrayJson(res.data.request_params)
  responseFields.value = parseArrayJson(res.data.response_fields)
  testResult.value = ''
}

async function runTest() {
  if (!selectedApiId.value) return
  try {
    const payload = testPayload.value ? JSON.parse(testPayload.value) : {}
    const res = await apiClient.runTest(selectedApiId.value, payload)
    testResult.value = JSON.stringify(res, null, 2)
  } catch (e) {
    testResult.value = JSON.stringify(e, null, 2)
  }
}

onMounted(async () => {
  await loadCategories()
  await loadApis()
})
</script>

<style scoped>
.layout { display: flex; gap: 16px; padding: 16px; height: calc(100vh - 40px); }
.card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(31,35,41,0.08); }
.sidebar { width: 240px; padding: 16px; overflow: auto; }
.main { flex: 1; padding: 16px; overflow: auto; }
.panel-title { font-size: 20px; font-weight: 600; margin-bottom: 12px; }
.hint { color: #86909c; font-size: 12px; }
.category-item { padding: 10px; border-radius: 6px; cursor: pointer; }
.category-item.active, .api-item.active { background: #e8f3ff; color: #0066cc; }
.header-row { display: flex; justify-content: space-between; align-items: center; }
.admin-link { color: #0066cc; font-size: 14px; }
.api-list { margin-top: 12px; border-top: 1px solid #f0f0f0; }
.api-item { display: flex; align-items: center; gap: 8px; padding: 10px; border-bottom: 1px solid #f5f5f5; cursor: pointer; }
.method { width: 50px; text-align: center; border-radius: 4px; color: #fff; font-size: 12px; }
.method.get { background: #1e88e5; }
.method.post { background: #43a047; }
.method.put { background: #f4511e; }
.method.delete { background: #e53935; }
.path { margin-left: auto; color: #86909c; font-size: 12px; }
.section-title { margin-top: 16px; margin-bottom: 8px; font-size: 16px; font-weight: 600; }
.desc { color: #4e5969; }
.meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.label { color: #86909c; margin-right: 8px; }
.table-wrap { border: 1px solid #ebeef5; border-radius: 6px; overflow: hidden; }
.table-row { display: grid; grid-template-columns: 1fr 1fr 1fr 2fr; gap: 8px; padding: 8px; border-bottom: 1px solid #f2f3f5; font-size: 13px; }
.table-row.head { background: #f7f8fa; font-weight: 600; }
.code-area { width: 100%; min-height: 120px; border: 1px solid #dcdfe6; border-radius: 6px; padding: 8px; font-family: monospace; margin-bottom: 8px; }
.btn { background: #0066cc; color: #fff; margin-bottom: 8px; }
</style>
