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
                        <h3 class="card-title">All Questions in Test {{testname}}</h3>

                        <div class="card-tools float-right">
                            <router-link class="btn btn-primary"
                                         :to="{ name: 'view_course', params: { id: form.test_id }}">
                                <i class="fa fa-arrow-left"></i> Back
                            </router-link>
                            <button class="btn btn-primary " @click="newModal">Add Question &nbsp;<i class="fas fa-book-open"></i></button>
                            <router-link class="btn btn-primary"
                                         :to="{ name: 'preview_test', params: { id: test_id }}">
                                <i class="fa fa-eye"></i> Preview Test
                            </router-link>

                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Question</th>
                                <th>Type</th>
                                <th>Answer(s)</th>
                                <th>Added At</th>
                                <th>Modify</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="question in questions.data" :key="question.id">
                                <td>{{question.id}}</td>
                                <td>{{question.question}}</td>
                                <td>{{question.type}}</td>
                                <td>{{getQuestionAnswer(question.answers)}}</td>
                                <td>{{question.created_at | myDate}}</td>
                                <td>
                                    <a href="#" @click="editModal(question)">
                                        <i class="fa fa-edit orange"></i>
                                    </a>
                                    /
                                    <a href="#" @click="deleteQuestion(question.id)">
                                        <i class="fa fa-trash red"></i>
                                    </a>

                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <pagination :data="questions" @pagination-change-page="getQuestionResults"></pagination>
                    </div>
                </div>
                <div v-if="!($gate.isAdmin())">
                    <not-found></not-found>
                </div>
                <!-- /.card -->
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" v-if="$gate.isAdmin()" id="addNew" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-show="!editMode" class="modal-title">Add New</h5>
                        <h5 v-show="editMode" class="modal-title">Update Question's Info</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="editMode ? updateQuestion() : createQuestion()">
                        <div class="modal-body">
                            <!--input edit form -->

                            <div class="form-group">
                                <select v-model="form.type" name="type" @change="checkQuestionType(form.type)"
                                        class="form-control" :class="{ 'is-invalid': form.errors.has('type') }">
                                    <option value="">Select Question Type</option>
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="true_or_false">True or False</option>
                                    <option value="matching_items">Matching Items</option>
                                </select>
                                <has-error :form="form" field="type"></has-error>
                            </div>

                            <div class="form-group">
                                <textarea v-model="form.question" name="question"
                                          placeholder="Question Text"
                                          class="form-control" :class="{ 'is-invalid': form.errors.has('question') }"></textarea>
                                <has-error :form="form" field="question"></has-error>
                            </div>

                            <div class="form-group" v-if="showBlock==='answers'">
                                <div class="row">
                                    <h3 class="card-title col-md-8">All Answers for the Question {{testname}}</h3>
                                    <button class="btn btn-primary float-right col-md-4" @click.prevent="AddAnswerField" v-if="form.type!== 'true_or_false' ">Add Answer</button>
                                </div>
                                <div class="row separator" v-for="(answer,index) in form.answers" track-by="$index">
                                    <div class="col-md-2">
                                        <button class="btn btn-danger float-right" @click.prevent="RemoveAnswerField(index)"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <textarea v-model="form.answers[index].answer" :name="`answers.${index}.answer`"
                                                      placeholder="Answer Text"
                                                      class="form-control" :class="{ 'is-invalid': form.errors.has(`answers.${index}.answer`) }">{{answer.answer}}</textarea>
                                            <has-error :form="form" :field="`answers.${index}.answer`"></has-error>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input v-model="form.answers[index].isAnswer" type="checkbox" :name="`answers.${index}.isAnswer`" :class="{ 'is-invalid': form.errors.has(`answers.${index}.isAnswer`) }" > Answer?
                                            <has-error :form="form" :field="`answers.${index}.isAnswer`"></has-error>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" v-if="showBlock==='true_false'">
                                <div class="row">
                                    <h3 class="card-title col-md-8">Answer for the Question {{testname}}</h3>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                <input v-model="form.answers[0].answer" type="radio" :name="`answers.0.answer`" :class="{ 'is-invalid': form.errors.has(`answers.0.answer`) }" value="true" :checked="form.answers[0].answer === 'true' "> True
                                            </label>
                                            <br>
                                            <label>
                                                <input v-model="form.answers[0].answer" type="radio" :name="`answers.0.answer`" :class="{ 'is-invalid': form.errors.has(`answers.0.answer`) }" value="false" :checked="form.answers[0].answer === 'false'"> False
                                            </label>
                                            <has-error :form="form" :field="`answers.0.answer`"></has-error>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" v-if="showBlock==='lists'">
                                <div class="row">
                                    <div class="form-group col-sm-12">
                                        <h3 class="card-title col-md-8">All Lists for the Question {{testname}}</h3>
                                        <button class="btn btn-primary float-right col-md-4" @click.prevent="AddListsField">Add List</button>
                                    </div>
                                    <p class="red col-md-12">* Note: Any Part B item in the same row with Part A item will be considered as the matching answer.</p>
                                </div>

                                <div class="row separator" v-for="(item,index) in form.matching_items" track-by="$index">
                                    <div class="col-md-2">
                                        <button class="btn btn-danger float-right" @click.prevent="RemoveListField(index)"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <textarea v-model="form.matching_items[index].partA" :name="`matching_items.${index}.partA`"
                                                      placeholder="Part A Item"
                                                      class="form-control" :class="{ 'is-invalid': form.errors.has(`matching_items.${index}.partA`) }">{{item.partA}}</textarea>
                                            <has-error :form="form" :field="`matching_items.${index}.partA`"></has-error>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <textarea v-model="form.matching_items[index].partB" :name="`matching_items.${index}.partB`"
                                                      placeholder="Part B Item"
                                                      class="form-control" :class="{ 'is-invalid': form.errors.has(`matching_items.${index}.partB`) }">{{item.partB}}</textarea>
                                            <has-error :form="form" :field="`matching_items.${index}.partB`"></has-error>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            <button v-show="!editMode" type="submit" class="btn btn-primary">Create</button>
                            <button v-show="editMode" type="submit" class="btn btn-warning">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                editMode:true,
                showBlock:'',
                test_id: this.$route.params.id,
                testname: '',
                description: '',
                course_id: '',
                image: '',
                questions:{},
                lists:[],
                form : new Form({
                    test_id: this.$route.params.id,
                    id:'',
                    answers: [],
                    question: '',
                    type:'',
                    matching_items:[]
                })
             }
        },
        methods: {
            getQuestionResults(page=1){
                axios.get('/api/questions?page='+page)
                    .then(response => {
                        this.questions = response.data;
                    })
            },
            createQuestion(){
                this.$Progress.start();
                this.form.post('/api/questions')
                    .then(() => {
                        //fire an event
                        Fire.$emit('QuestionCreated');

                        $('#addNew').modal('hide');

                        toast.fire({
                            icon: 'success',
                            title: 'Question created successfully'
                        });

                        this.$Progress.finish();
                    })
                    .catch( () => {
                        this.$Progress.fail();
                    });
            },
            updateQuestion(){
                this.$Progress.start();
                this.form.put('/api/questions/'+this.form.id)
                    .then(() => {
                        //fire an event
                        Fire.$emit('QuestionUpdated');

                        $('#addNew').modal('hide');

                        toast.fire({
                            icon: 'success',
                            title: 'Question updated successfully'
                        });

                        this.$Progress.finish();
                    })
                    .catch( () => {
                        this.$Progress.fail();
                    });
            },
            loadQuestions(){
                if (this.$gate.isAdmin() || this.$gate.isAuthor()){
                    axios.get("/api/questions/test/"+this.$route.params.id)
                        .then(({ data }) => {
                            this.questions = data;
                        })

                }

            },
            deleteQuestion(id){
                swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value)
                    {
                        this.form.delete('/api/questions/'+id)
                            .then(() => {
                                swal.fire(
                                    'Deleted!',
                                    'Question has been deleted.',
                                    'success'
                                );
                                Fire.$emit('QuestionDeleted');
                            })
                            .catch(() => {
                                swal.fire("Failed","There was something wrong.","warning");
                            })
                    }

                });

            },
            editModal(question){
                this.editMode = true;
                this.form.reset();
                $('#addNew').modal('show');
                this.form.fill(question);
                this.checkQuestionType(this.form.type);
            },
            newModal(){
                this.editMode = false;
                this.form.reset();
                this.checkQuestionType('');
                $('#addNew').modal('show')
            },
            loadTestInfo(){
                axios.get("/api/tests/"+this.$route.params.id)
                    .then(({ data }) => {
                        let test = data;
                        this.testname = test.name;
                        this.description = test.description;
                        this.duration = test.duration;
                        this.course_id = test.course.id;
                    })
            },
            AddListsField() {
                this.form.matching_items.unshift({ partA: '',partB:''});
            },
            AddAnswerField() {
                this.form.answers.unshift({ answer: '',isAnswer:false });
            },
            RemoveAnswerField(index){
                this.form.answers.splice(index,1);
            },
            RemoveListField(index){
                this.form.matching_items.splice(index,1);
            },
            checkQuestionType(value){
                this.showBlock = '';

                if(value === 'multiple_choice'){
                    this.showBlock = 'answers';
                    if (this.form.answers === undefined){
                        this.form.answers = [];
                        this.AddAnswerField();
                    }
                }
                else if(value === 'matching_items'){
                    this.showBlock = 'lists';
                    if (this.form.matching_items === undefined){
                        this.form.matching_items = [];
                        this.AddListsField();
                    }
                }
                else if(value === 'true_or_false'){
                    this.showBlock = 'true_false';
                    if (this.form.answers === undefined){
                        this.form.answers = [];
                    }
                    else if (this.form.answers.length <= 0){
                        this.AddAnswerField();
                    }
                    this.form.answers[0].isAnswer = true;
                }
                else {

                }

            },
            getQuestionAnswer(answers){
                let answer = '';
                answers.forEach(function (item) {
                    if (item.isAnswer){
                        answer += item.answer+', ';
                    }
                });
                answer = answer.replace(/, $/,'');
                return answer;
            }
        },
        created() {
            this.loadTestInfo();
            this.loadQuestions();
            Fire.$on('QuestionCreated', () => {
                this.loadQuestions();
            });
            Fire.$on('QuestionDeleted', () => {
                this.loadQuestions();
            });
            Fire.$on('QuestionUpdated', () => {
                this.loadQuestions();
            });
            Fire.$on('Searching', () => {
                let query = this.$parent.search;
                axios.get("/api/findQuestion?q="+query)
                    .then(({ data }) => {this.questions = data})
                    .catch(() => {

                    })
            });
            // setInterval(() => this.loadQuestions(),3000);
        }
    }
</script>
