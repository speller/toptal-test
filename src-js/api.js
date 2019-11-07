import axios from 'axios'
import isObject from 'lodash-es/isObject'

const API_BASE_URL = 'http://localhost:8000'

export const apiCall = async (httpMethod, actionUrl, payload, accessToken) => {
  let headers = {
  }
  if (accessToken) {
    headers['Authentication'] = `Bearer ${accessToken}`
  }
  return axios({
    url: actionUrl,
    baseURL: API_BASE_URL,
    method: httpMethod,
    data: httpMethod === 'post' ? {...payload} : {},
    params: httpMethod === 'get' ? {...payload} : {},
    headers,
  })
}

export const apiCallPost = (actionUrl, payload, accessToken) => {
  return apiCall('post', actionUrl, payload, accessToken)
}

export const apiCallGet = (actionUrl, payload, accessToken) => {
  return apiCall('get', actionUrl, payload, accessToken)
}

export function validateApiResult(result) {
  if (result.status !== 200) {
    throw new Error(`API call failed with HTTP response code ${result.status}${`: ${getMessageFromResponse(result)}` ?? ''}`)
  }
  const apiResult = result.data
  if (!apiResult) {
    throw new Error('Undefined API result')
  }
  if (!isObject(apiResult)) {
    throw new Error('Invalid API call result. Object expected.')
  }
  if (!apiResult.success) {
    throw new Error(`API call failed with message: ${getMessageFromResponse(result)}.`)
  }
  return apiResult
}

export function getMessageFromResponse(response) {
  return response.data ? response.data.msg ?? '' : ''
}
