<script setup>

import {ref, onMounted, inject } from 'vue'
import axios from 'axios'
import { useQuasar } from 'quasar'
import { useI18n } from 'vue-i18n'

const $q = useQuasar()

const axiosConfig = {
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Access-Control-Allow-Origin': '*'
  }
}

const questionId = ref(1)
const question = ref({})
const answer = ref("")

const wsRoute = inject('wsroute')

onMounted(() => {
  let queryString = window.location.search
  let urlParams = new URLSearchParams(queryString)
  if( urlParams.has('question') ){
      questionId.value = urlParams.get('question')
  }
    
  loadQuestion(questionId.value)
})


  async function loadQuestion(questionId, next) {
    try {
      const response = await axios({
        method: 'get',
        url: window.location.protocol + "//" + window.location.hostname  + wsRoute + "?_rest=Questions/" + questionId + (next? '?next': '')
      }, axiosConfig)
      console.log(response)
      question.value = response.data.object 
    } catch (error) {
      processError(error)
      
    }
  }
  
  async function storeAnswer(){
    const msg = {
      'question_id': question.value.id,
      'content': answer.value
    }
    
    try {
      $q.loading.show({
        delay: 400 // ms
      })
      const response = await axios({
        method: 'post',
        url: window.location.protocol + "//" + window.location.hostname  + wsRoute + "?_rest=Answers",
        data: msg
      }, axiosConfig)
      console.log(response)
      answer.value = ""
      loadQuestion(question.value.id, true)
      $q.loading.hide()
    } catch (error) {
      processError(error)
      
    }
  }
  
  function processError(error) {
    /*if (error.response?.status === 401) {
        $q.dialog({
          title: t('error_title'),
          message: t('global_missing_session') 
        })  
    } else {
      $q.dialog({
        title: t('error_title'),
        message: error.response?.data?.message ? error.response.data.message : error 
      })  
    }*/
    if (error.response?.status === 401) {
        $q.dialog({
          title: 'error',
          message: 'missing session'
        })  
    } else {
      $q.dialog({
        title: 'error',
        message: error.response?.data?.message ? error.response.data.message : error 
      })  
    }
  }

</script>

<template>
  <div class="q-pa-md row items-start q-gutter-md">
    <q-card class="my-card">
      <q-card-section>
        {{ question.question }}
      </q-card-section>
      
      <q-card-section>
        <q-input v-model="answer" label="Digita tu respuesta" />
      </q-card-section>
      
      <q-card-actions>
        <q-btn flat @click="storeAnswer()">Continuar</q-btn>
      </q-card-actions>
    </q-card>
  </div>
  
</template>


<style scoped lang="scss">

</style>