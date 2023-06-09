<script setup>

import {ref, onMounted, inject } from 'vue'
import axios from 'axios'
import { useQuasar } from 'quasar'
import { useI18n } from 'vue-i18n'

const $q = useQuasar()

const headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Access-Control-Allow-Origin': '*'
}

const axiosConfig = {headers}

const questionId = ref(1)
const question = ref({})
const answer = ref([""])
const options = ref({})
const questionType = ref(0)
const panel = ref('s0')
const promoCode = ref('')
const logged = ref(false)
const user = ref({})
const plans = ref(false)
const oneStart = ref(1)
const plansList = ref([{
  name: "Básico",
  stars: 1,
  includes: ["a", "b", "c"]
},{
  name: "Avanzado",
  stars: 2,
  includes: ["a", "b", "c"]
},{
  name: "Experto",
  stars: 3,
  includes: ["a", "b", "c"]
}])

const wsRoute = inject('wsroute')

const data = {
  userId: 789,
  // other properties you want to include in the request body
};

onMounted(async() => {
  let queryString = window.location.search
  let urlParams = new URLSearchParams(queryString)
  if( urlParams.has('question') ){
      questionId.value = urlParams.get('question')
      loadQuestion(questionId.value)
  }
  if( urlParams.has('code') ){
      promoCode.value = urlParams.get('code')
      await validateCode()
  }

})

  

  async function loadQuestion(questionId, next) {
    try {
      const response = await axios({
        method: 'post',
        url: window.location.protocol + "//" + window.location.hostname  + wsRoute + "?_rest=Questions/" + questionId + (next? '?next': ''),
        data: {
          userId: user.value.id,
        },
      }, axiosConfig)
      
      console.log(response)
      question.value = response.data.object 
      questionType.value = response.data.object.question_type 
      options.value = response.data.object.questions ? 
                        JSON.parse(response.data.object.questions) : {};
                        return;
    } catch (error) {
      processError(error)
    }
  }
  
  async function storeAnswer(){
    if (!answer.value || answer.value.toString().length === 0) {
      $q.dialog({
        title: 'Contesta las preguntas',
        message: 'Antes de continuar asegúrate de contestar las preguntas'
      })
      return;
    }
    const msg = {
      'question_id': question.value.id,
      'content': answer.value.join(' - '),
      'user_id': user.value.id
    }
    
    try {
      $q.loading.show({
        message: 'Procesando...en preguntas complejas, el sistema se puede tomar hasta un minuto en contestar',
        delay: 400 // ms
      })
      const response = await axios({
        method: 'post',
        url: window.location.protocol + "//" + window.location.hostname  + wsRoute + "?_rest=Answers",
        data: msg
      }, axiosConfig)
      console.log(response)
      answer.value = [""]
      await loadQuestion(question.value.id, true)
      $q.loading.hide()
    } catch (error) {
      processError(error)
      
    }
  }
  
  function showNoInputDialog(value) {
    if (!value || value.length === 0) {
      $q.dialog({
        title: 'Completa la información',
        message: 'Antes de continuar asegúrate de llenar todos los campos'
      })
      return false
    }
    return true
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
      $q.loading.hide()
    }
    throw new Error(error)
  }
  
  async function resetForm(){
    $q.dialog({
        title: 'Estás seguro?',
        message: 'Ésta operación no se puede reversar, por favor asegúrate de guardar toda la información necesaria' 
      }).onOk(async() => {
        // console.log('OK')
        $q.loading.show({
          delay: 400 // ms
        })
        const response = await axios({
          method: 'put',
          url: window.location.protocol + "//" + window.location.hostname  + wsRoute + "?_rest=Answers",
          data: {
            userId: user.value.id,
          },
        }, axiosConfig)
        console.log(response)
        answer.value = [""]
        loadQuestion(1)
        $q.loading.hide()
      })
  }
  
  async function validateCode(){
    if (!showNoInputDialog(promoCode.value)){
      return
    }
    $q.loading.show({
      message: 'Procesando...en preguntas complejas, el sistema se puede tomar hasta un minuto en contestar',
      delay: 400 // ms
    })
    const response = await axios({
      method: 'get',
      url: window.location.protocol + "//" + window.location.hostname  + wsRoute + "?_rest=EarlyAccessUsr/" + promoCode.value + "?code"
    }, axiosConfig)
    console.log(response)
    if (!response.data.success) {
      $q.dialog({
        title: 'Verifica tu código!',
        message: 'El código que proporcionaste no fue encontrado, por favor verifícalo' 
      })
      $q.loading.hide()
    } else {
      $q.dialog({
        title: 'Bienvenid@ ' + response.data.object.name,
        message: 'Tomemonos unos minutos para trabajar en algún reto que tengas en tu trabajo o negocio actualmente'
      })
      logged.value = true
      user.value = response.data.object 
      await loadQuestion(questionId.value)
      $q.loading.hide()
    }
  }
  
  async function viewMore(){
    if (user.value.generated === 0){
      $q.dialog({
        title: 'Atención',
        message: 'En el modo prueba, solo puedes generar un detalle, adquiere alguno de nuestros paquetes para liberar todo el poder!',
        cancel: true,
      })
    } else {
      $q.dialog({
        title: 'Atención',
        message: 'Lo sentimos, ya usaste tu prueba gratis, por favor adquiere un plan!'
      })
    }
  }
  
</script>

<template>
  <div v-if="!logged" class="q-pa-md row items-start q-gutter-md">
    <q-card
      class="my-card"
    >
      <q-card-section>
        <div class="text-h6">Bienvenido a Trotalo Coach</div>
        <br>
        <div class="text-subtitle2">El Coach que necesitas para alcanzar los objetivos de tu negocio!</div>
        <br>
        <div class="text-subtitle2">Libera todo el poder de tus ideas y has crecer tu negocio con nuestras herramientas de Inteligencia Artificial</div>
        <br>
        <div class="text-subtitle2">Obten asesoría experta, planes concretos, y estrategias personalizasa para lleavr tu negocio al siguiente nivel!</div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <div class="text-subtitle2">Ingresa tu código de invitación!</div>
        <q-input v-model="promoCode" label="Código validación" />
      </q-card-section>
      <div class="q-pa-md">
        <q-btn color="primary" @click="validateCode()">Validar</q-btn>
      </div>
    </q-card>
  </div>
  <div 
    v-else 
    class="q-pa-md row items-start q-gutter-md">
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
          label-slot
          type="textarea"
        >
          <template v-slot:label>
            <div class="row items-center all-pointer-events question">
              {{q}}
            </div>
          </template>
        </q-input>
        
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
                { label: 'Semana 4', value: 's3' }
              ]"
            />
            <q-tab-panels v-model="panel" animated class="shadow-2 rounded-borders">
              <q-tab-panel 
                v-for="(action, key) in options" :key="key"
                :name="'s' + key">
                <div class="text-h6">{{action.nombre}}</div>
                {{action.indicadores}}
                <div class="text-h6">Para poder alcanzar tus objetivos, ésta semana deberías:</div>
                <q-btn  
                  v-for="(task, tasKey) in action.actividades" 
                  :key="tasKey"
                  color="secondary" 
                  :label="task" 
                  class="full-width q-mb-md"
                  @click="plans = true"/>
              </q-tab-panel>
            </q-tab-panels>
          </div>
        </div>
      </q-card-section>
      
      <q-card-section v-else>
        <q-input 
          v-model="answer[0]"
          label="Digita tu respuesta" 
          type="textarea" />
      </q-card-section>

      <div class="q-pa-md">
        <q-btn-group spread>
          <q-btn color="secondary" @click="resetForm()" icon="visibility">Volver a empezar</q-btn>  
          <q-btn color="primary" @click="storeAnswer()">Siguiente</q-btn>
        </q-btn-group>
      </div>
    </q-card>
    
    <q-dialog v-model="plans" persistent full-width transition-show="flip-down" transition-hide="flip-up">
      <q-card>
        <q-toolbar>
          <q-avatar>
            <img src="https://cdn.quasar.dev/logo-v2/svg/logo.svg">
          </q-avatar>
          <q-toolbar-title><span class="text-weight-bold">Trotalo</span> Coach</q-toolbar-title>
          <q-btn color="secondary" round dense icon="close" v-close-popup />
        </q-toolbar>
        <q-card-section>
          <div class="text-center">
            <q-btn color="secondary" @click="viewMore" label="Ver más detalles!" />
          </div>
          
          <div class="text-h6 text-center">o</div>
          
          <div class="text-h6 text-center">Selecciona uno de nuestros planes para desbloquear todo el poder!</div>
        </q-card-section>
        
        <div class="row">
          <div class="col" v-for="(plan, key) in plansList" :key="key">
            <q-card class="my-card">
              <q-img src="https://cdn.quasar.dev/img/chicken-salad.jpg" />
              <q-card-section class="text-center">
                <div class="row no-wrap items-center">
                  <div class="col text-h6 ellipsis">
                    {{plan.name}}
                  </div>
                </div>
      
                <q-rating v-model="plan.stars" :max="plan.stars" size="32px" />
              </q-card-section>
      
              <q-card-section class="q-pt-none">
                <div class="text-subtitle1">
                  Incluye
                </div>
                <div class="text-caption text-grey">
                  <ul>
            				<li v-for="(include, key) in plan.includes" :key="key">{{include}}</li>
            			</ul>
                </div>
              </q-card-section>
      
              <q-separator />
      
              <q-card-actions align="center">
                <q-btn color="primary" label="Solicitar!" />
              </q-card-actions>
            </q-card>
          </div>
        </div>
      </q-card>
    </q-dialog>
  </div>
</template>


<style scoped lang="scss">
.question{
  overflow-wrap: break-word;
  white-space: normal;
  padding-bottom: 35px;
  padding-top: 25px;
}

.my-card {
  max-width: 500px;
  margin: 0 auto;
  padding: 20px;
  color: #13435E;
}

.text-h6 {
  font-size: 20px;
  font-weight: bold;
  color: #13435E;
}

.text-subtitle2 {
  font-size: 16px;
  color: #13435E;
}

.q-pt-none {
  padding-top: 0 !important;
}

.q-pa-md {
  padding: 20px;
}

.row {
  display: flex;
  flex-wrap: wrap;
}

.items-start {
  align-items: flex-start;
}

.q-gutter-md {
  margin: -16px;
}

.question {
  font-size: 16px;
  font-weight: bold;
  color: #219EBC;
  margin-bottom: 10px;
}

.full-width {
  width: 100%;
}

.q-mb-md {
  margin-bottom: 16px;
}

.shadow-2 {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.rounded-borders {
  border-radius: 8px;
}

.text-h6 {
  font-size: 20px;
  font-weight: bold;
  color: #219EBC;
}

.text-h6,
.text-subtitle2 {
  margin-bottom: 10px;
}

.q-pa-md {
  padding: 20px;
}

.q-gutter-y-md > * {
  margin-top: 16px;
  margin-bottom: 16px;
}

.q-btn {
  background-color: #FFB703;
  color: #13435E;
}

.q-btn:hover {
  background-color: #F08B27;
}
</style>
