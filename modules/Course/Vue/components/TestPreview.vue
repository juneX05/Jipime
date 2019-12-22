<template>
    <div class="container">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">{{testname}} Test</h1>
                        <small>{{description}}</small>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>

        <div class="row">
            <div class="col-md-12" >

                <div v-if="$gate.isAdmin()" class="card">
                    <div class="card-header">
                        <h3 class="card-title">Attempt All Questions in Test {{testname}}</h3>
                        <router-link class="btn btn-primary float-right"
                                     :to="{ name: 'view_test', params: { id: form.test_id }}">
                            <i class="fa fa-arrow-left"></i> Back
                        </router-link>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">

                        <table class="table table-bordered" style="width: 100%">
                            <tr v-for="(question,index) in questions" :key="question.id">
                                <td>
                                    <div class="form-group" v-if="question.type === 'multiple_choice' ">
                                        <p>
                                            <span><b>{{'Qn '+(index+1)}}</b></span>
                                            {{question.question}}
                                        </p>
                                        <div class="form-group">
                                            <label v-for="choice in question.answers">
                                                <input type="radio"
                                                       v-model=" form.answers.multiple_choice['qn_'+question.id] "
                                                       :key="choice.id" :name=" 'qn_'+question.id" :value="choice.id"
                                                />
                                                {{choice.answer}} &nbsp;&nbsp;
                                            </label>
                                        </div>

                                    </div>
                                    <div class="form-group" v-if="question.type === 'true_or_false' ">
                                        <p>
                                            <span><b>{{'Qn '+(index+1)}}</b></span>
                                            {{question.question}}
                                        </p>
                                        <div class="form-group">
                                            <label>
                                                <input type="radio"
                                                       v-model=" form.answers.true_or_false['qn_'+question.id] "
                                                       :name=" 'qn_'+question.id " value="true"
                                                />
                                                True &nbsp;&nbsp;
                                            </label>
                                            <label>
                                                <input type="radio"
                                                       v-model=" form.answers.true_or_false['qn_'+question.id] "
                                                       :name=" 'qn_'+question.id " value="false"
                                                />
                                                False &nbsp;&nbsp;
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group" v-if="question.type === 'matching_items' ">
                                        <p>
                                            <span><b>{{'Qn '+(index+1)}}</b></span>
                                            {{question.question}}
                                        </p>

                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th>List A</th>
                                                <th>List B</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="item in reverseOrder(question.matching_items)" v-if="item.partA !== null">
                                                <td>{{item.partA}}</td>
                                                <td>
                                                    <select v-model=" form.answers.matching_items['qn_'+question.id+'_'+item.id] " v-if="item.partA !== null "
                                                            :name=" 'qn_'+question.id+'_'+item.id " class="form-control">
                                                        <option v-for=" answer in question.matching_items " :value="answer.id">
                                                            {{answer.partB}}
                                                        </option>
                                                    </select>
                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </table>




                    </div>
                </div>
                <div v-if="!($gate.isAdmin())">
                    <not-found></not-found>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                testname : '',
                description : '',
                duration : '',
                questions:{},
                form : new Form({
                    test_id: this.$route.params.id,
                    answers: {
                        matching_items:{},
                        multiple_choice:{},
                        true_or_false:{},
                    },
                })
             }
        },
        methods: {
            loadQuestions(){
                if (this.$gate.isAdmin() || this.$gate.isAuthor()){
                    axios.get("/api/questions/preview/test/"+this.$route.params.id)
                        .then(({ data }) => {
                            this.questions = data;
                        })
                        .then(() => {
                            let partA_items = [];
                            let partB_items = [];
                            let shuffler = this.$parent.shuffler;
                            this.questions.forEach(function (question) {
                                if (question.type === 'matching_items'){

                                    question.matching_items.forEach(function (item) {
                                        partA_items.push(item.partA);
                                        partB_items.push({id:item.id, partB:item.partB});
                                    });
                                    partB_items = shuffler(partB_items);
                                    question.matching_items.forEach(function (item, index) {
                                        question.matching_items[index] = {
                                            partA:partA_items[index],
                                            id:item.id,
                                            partB:item.partB
                                        }
                                    })
                                }
                                else if (question.type === 'multiple_choice'){
                                    question.answers = shuffler(question.answers);
                                }
                            })
                        })

                }

            },
            reverseOrder(items){
                return items.slice().reverse();
            },
            loadTestInfo(){
                axios.get("/api/tests/"+this.$route.params.id)
                    .then(({ data }) => {
                        let test = data;
                        this.testname = test.name;
                        this.description = test.description;
                        this.duration = test.duration;
                    })
            },
        },
        created() {
            this.loadTestInfo();
            this.loadQuestions();
            // setInterval(() => this.loadQuestions(),3000);
        }
    }
</script>
