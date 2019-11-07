import {
  apiCallGet,
  apiCallPost,
  getMessageFromResponse,
  validateApiResult,
} from '../../api'

function alertError(e) {
  alert(e.response ? getMessageFromResponse(e.response) : (e.message ? e.message : e))
}

export async function apiSignIn(login, password) {
  try {
    const result = await apiCallPost(
      '/signin',
      {
        login,
        password,
      },
    )
    return validateApiResult(result)
  } catch (e) {
    alertError(e)
    return false
  }
}

export async function apiSignUp(login, password, role) {
  try {
    const result = await apiCallPost(
      '/signup',
      {
        login,
        password,
        role,
      },
    )
    return validateApiResult(result)
  } catch (e) {
    alertError(e)
    return false
  }
}

export async function apiLoadTasks(dateBegin, dateLast, accessToken) {
  try {
    const result = await apiCallGet(
      '/task-list',
      {
        dateBegin,
        dateLast,
      },
      accessToken
    )
    const { data } = validateApiResult(result)
    return data
  } catch (e) {
    alertError(e)
    return false
  }
}

export async function apiCreateTask(taskData, accessToken) {
  try {
    const result = await apiCallPost(
      '/task-add',
      taskData,
      accessToken
    )
    const { data } = validateApiResult(result)
    return data
  } catch (e) {
    alertError(e)
    return null
  }
}

export async function apiUpdateTask(taskData, accessToken) {
  try {
    const result = await apiCallPost(
      '/task-update',
      taskData,
      accessToken
    )
    validateApiResult(result)
    return true
  } catch (e) {
    alertError(e)
    return false
  }
}

export async function apiDeleteTask(id, accessToken) {
  try {
    const result = await apiCallPost(
      '/task-delete',
      {id},
      accessToken
    )
    validateApiResult(result)
    return true
  } catch (e) {
    alertError(e)
    return false
  }
}

export async function apiLoadUsers(accessToken) {
  try {
    const result = await apiCallGet(
      '/users-list',
      {},
      accessToken
    )
    const { data } = validateApiResult(result)
    return data
  } catch (e) {
    alertError(e)
    return false
  }
}

export async function apiUpdateUser(id, workingHoursPerDay, accessToken) {
  try {
    const result = await apiCallPost(
      '/user-update',
      {
        id,
        workingHoursPerDay,
      },
      accessToken
    )
    validateApiResult(result)
    return true
  } catch (e) {
    alertError(e)
    return false
  }
}
