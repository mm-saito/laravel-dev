<template>
  <div class="card mb-5 profile_card" :class="{ loading : loading }">
    <!-- 更新メッセージ -->
    <div v-if="updated" class="alert alert-primary" role="alert">
      更新しました
    </div>
    <div v-if="error_flg" class="alert alert-danger" role="alert">
      <ul>
        <li v-for="error in errors">
          <p v-for="msg in error">
            {{msg}}
          </p>
        </li>
      </ul>
    </div>
    <!-- ロード画面 -->
    <div v-if="loading" class="spinner-border text-primary profile_spiner" role="status">
      <span class="sr-only">Loading...</span>
    </div>
    <form v-if="!loading" class="row no-gutters">
      <div class="profile_info" :class="[ edit_flg === true ? 'col-md-7' : 'col-md-5' ]">

        <!-- 画像表示エリア -->
        <div v-if="!edit_flg"class="img_box">
          <img class="card-img-top profile_img" :src="img_file"/>
        </div>
        <!-- 画像編集エリア -->
        <div v-else class="img_box">
          <div data-toggle="modal" data-target="#img_modal" data-whatever="@president">
            <img class="card-img-top profile_img gray_out" :src="img_file"/>
          </div>
          <div class="edit_photo">
            <i class="fui fui-plus"></i>
          </div>
        </div>
        <!-- モーダル -->
        <drop-img :user_id="user_id" @ImgError="catchError"></drop-img>

        <!-- 左側通常 -->
        <dl v-if="!edit_flg">
          <dt>性別</dt>
          <dd>{{ gender }}</dd>
          <dt>生年月日</dt>
          <dd>{{ req.birthday }}</dd>
          <dt>好きな食べ物</dt>
          <dd>{{ req.favorite_food }}</dd>
          <dt>嫌いな食べ物</dt>
          <dd>{{ req.hated_food }}</dd>
        </dl>
        <!-- 左側編集 -->
        <dl v-else>
          <dt>性別</dt>
          <dd class="form-group">
            <gender-select :gender="req.gender" />
          </dd>
          <dt>生年月日</dt>
          <dd class="form-group">
            <birth-day-select :birth_day="req.birthday" />
          </dd>
          <dt>好きな食べ物</dt>
          <dd>
            <input type="text" name="gender" class="form-control" v-model="req.favorite_food">
          </dd>
          <dt>嫌いな食べ物</dt>
          <dd>
            <input type="text" name="gender" class="form-control" v-model="req.hated_food">
          </dd>
        </dl>
      </div><!-- 左側END -->

      <!-- 右側通常(レーダーチャート) -->
      <div v-if="!edit_flg" :class="[ edit_flg === true ? 'col-md-5' : 'col-md-7' ]">
        <div class="card-body">
          <div>
            <h5 class="card-title">
              {{req.name}}
            </h5>
          </div>
          <profile-chart :personality="req"></profile-chart>
        </div>
      </div>
      <!-- 右側編集エリア -->
      <div v-else class="col-md-5">
        <div class="card-body">
          <dl>
            <dt>なまえ</dt>
            <dd>
              <input type="text" name="name" class="form-control" v-model="req.name">
            </dd>
            <dt>ＨＰ</dt>
            <dd class="form-group">
              <param-select :param="req.personality_1" :num="1" />
            </dd>
            <dt>ＭＰ</dt>
            <dd class="form-group">
              <param-select :param="req.personality_2" :num="2" />
            </dd>
            <dt>こうげき</dt>
            <dd class="form-group">
              <param-select :param="req.personality_3" :num="3" />
            </dd>
            <dt>しゅび</dt>
            <dd class="form-group">
              <param-select :param="req.personality_4" :num="4" />
            </dd>
            <dt>すばやさ</dt>
            <dd class="form-group">
              <param-select :param="req.personality_5" :num="5" />
            </dd>
            <dt>かしこさ</dt>
            <dd class="form-group">
              <param-select :param="req.personality_6" :num="6" />
            </dd>
          </dl>
        </div>
      </div>
    </form><!-- wrrap END -->
    <!-- ボタンエリア -->
    <div v-if="!edit_flg && !loading && is_mine" class="form-group btn_group">
      <button type="button" class="btn btn-primary edit_btn" @click="(edit_flg = true)">
        <i class="fa fa-pencil"></i>
      </button>
    </div>
    <div v-else-if="!loading && is_mine" class="form-group btn_group">
      <button type="button" class="btn btn-info edit_btn" @click="update">
        <i class="fui fui-check"></i>
      </button>
      <button type="button" class="btn btn-danger edit_btn" @click="(edit_flg = false)">
        <i class="fui fui-cross"></i>
      </button>
    </div>
  </div>
</template>

<script>
import Vue from 'vue'
import ProfileChart from './ProfileChart'
import DropImg from './DropImg'
import ParamSelect from './ParamSelect'
import BirthDaySelect from './BirthDaySelect'
import GenderSelect from './GenderSelect'


export default {
    
  // チャートコンポーネント
  components: {
      ProfileChart
  },
  // bladeからデータを受け取り
  props:['profile'],
  data() {
    return {
      // APIでpostするためのreq
      req: {
        name: this.profile.name,
        gender: this.profile.gender,
        birthday: this.profile.birthday,
        favorite_food: this.profile.favorite_food,
        hated_food: this.profile.hated_food,
        personality_1: this.profile.personality_1,
        personality_2: this.profile.personality_2,
        personality_3: this.profile.personality_3,
        personality_4: this.profile.personality_4,
        personality_5: this.profile.personality_5,
        personality_6: this.profile.personality_6,
      },
      user_id: this.profile.user_id,
      img_file: this.profile.img_file, // アップロード画像ファイル名
      gender: '',
      edit_flg: false,
      updated: false,
      loading: false,
      errors: [],
      error_flg: false,
      is_mine: this.profile.is_mine,
    }
  },

  mounted: function () {
    // 性別の翻訳値を設定
    this.gender = this.setGender(this.profile.gender)
  },

  methods: {
    // 性別設定
    setGender(gender) {
      if (gender == 0) {
        return '男'
      }
      this.is_woman = true
      return '女'
    },
    // 更新処理
    update() {
      this.loading = true
      axios.patch('/api/profiles/' + this.profile.id, this.req).then(res => {
          this.edit_flg = false
          this.updated = true
          // 更新後も翻訳値を設定
          this.gender = this.setGender(this.req.gender)
          this.loading = false
      })
      .catch(err => {
        this.errors = err.response.data.errors;
        this.error_flg = true
        // グラフの値を初回読み込み時の状態に戻す
        this.cansel()
        this.loading = false
      });
    },
    // DBの値にセットし直す
    cansel() {
      this.req.name = this.profile.name
      this.req.gender = this.profile.gender
      this.req.birthday = this.profile.birthday
      this.req.favorite_food = this.profile.favorite_food
      this.req.hated_food = this.profile.hated_food
      this.req.personality_1 = this.profile.personality_1
      this.req.personality_2 = this.profile.personality_2
      this.req.personality_3 = this.profile.personality_3
      this.req.personality_4 = this.profile.personality_4
      this.req.personality_5 = this.profile.personality_5
      this.req.personality_6 = this.profile.personality_6
    },
    catchError(e) {
      console.log('!!')
      this.errors = e.response.data.errors;
      this.error_flg = true
      // グラフの値を初回読み込み時の状態に戻す
      this.cansel()
      this.loading = false
    }
  }
}
Vue.component('profile-chart', ProfileChart)
Vue.component('drop-img', DropImg)
Vue.component('param-select', ParamSelect)
Vue.component('birth-day-select', BirthDaySelect)
Vue.component('gender-select', GenderSelect)

</script>
