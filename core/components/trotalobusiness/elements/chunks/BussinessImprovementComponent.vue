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
const answer = ref([""])
const options = ref({})
const questionType = ref(0)
const panel = ref('s0')

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
      questionType.value = response.data.object.question_type 
      options.value = response.data.object.questions ? 
                        JSON.parse(response.data.object.questions) : {};
    } catch (error) {
      processError(error)
      
    }
  }
  
  async function storeAnswer(){
    if (!answer.value || answer.value.length === 0) {
      $q.dialog({
        title: 'Contesta las preguntas',
        message: 'Antes de continuar asegurate de contestar las preguntas'
      })
      return;
    }
    const msg = {
      'question_id': question.value.id,
      'content': answer.value.join(' - ')
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
      answer.value = [""]
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
  
  async function resetForm(){
    $q.dialog({
        title: 'Estas seguro?',
        message: 'Esta operacion no se puede reversar, porfavor asegurate de guardar toda la informacion necesaria' 
      }).onOk(async() => {
        // console.log('OK')
        $q.loading.show({
          delay: 400 // ms
        })
        const response = await axios({
          method: 'put',
          url: window.location.protocol + "//" + window.location.hostname  + wsRoute + "?_rest=Answers"
        }, axiosConfig)
        console.log(response)
        answer.value = [""]
        loadQuestion(1)
        $q.loading.hide()
      })
  }

</script>

<!--<template>
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
  
</template>-->
<template>
  <div class="q-pa-md row items-start q-gutter-md">
    <q-card class="my-card">
      <q-card-section>
        {{ question.question }}
      </q-card-section>
      <br>
      <q-card-section v-if="question.questions">
        <q-input
          v-if="questionType === 1"
          v-for="(q, key) in options" :key="key"
          v-model="answer[key]"
          :label="q"
        />
        
        <q-btn v-if="questionType === 2"
            v-for="(q, key) in options" :key="key"
            v-model="answer[key]"
            color="primary"
            class="full-width q-mb-md" 
            :label="q"
            @click="answer[0] = q; storeAnswer()"/>
        
        <div
            v-if="questionType === 3"
            v-for="(option, index) in questionOptions"
            :key="index"
          >
            <q-checkbox
              v-model="answer[key]"
              :label="option"
            />
        </div>
        
        <div v-if="questionType === 4" class="q-pa-md">
          <div class="q-gutter-y-md">
            <q-option-group
              v-model="panel"
              inline
              :options="[
                { label: 'Semana 1', value: 's0' },
                { label: 'Semana 2', value: 's1' },
                { label: 'Semana 3', value: 's2' },
                { label: 'Semana 4', value: 's3' },
                { label: 'Semana 5', value: 's4' }
              ]"
            />
            <q-tab-panels v-model="panel" animated class="shadow-2 rounded-borders">
              <q-tab-panel 
                v-for="(action, key) in options" :key="key"
                :name="'s' + key">
                <div class="text-h6">{{action.nombre}}</div>
                {{action.indicadores}}
                <div class="text-h6">Para poder alcanzar tus objetivos, esta semana deberias:</div>
                <q-btn  
                  v-for="(task, tasKey) in action.actividades" 
                  :key="tasKey"
                  color="secondary" 
                  :label="task" 
                  class="full-width q-mb-md"/>
              </q-tab-panel>
            </q-tab-panels>
          </div>
        </div>
  
        
          
      </q-card-section>
      
      <q-card-section v-else>
        <q-input v-model="answer[0]" label="Digita tu respuesta" />
      </q-card-section>

      <div class="q-pa-md">
        <q-btn-group spread>
          <q-btn color="primary" @click="storeAnswer()">Siguiente</q-btn>
          <q-btn color="secondary" @click="resetForm()" icon="visibility">Volver a empezar</q-btn>  
          <!--<q-btn color="purple" label="First" icon="timeline" />
          <q-btn color="purple" label="Second" icon="visibility" />-->
        </q-btn-group>
      </div>
    </q-card>
  </div>
</template>


<style scoped lang="scss">

</style>