<template>
    <div class="container">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">{{coursename}} Course</h1>
                        <small>{{description}}</small>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>

        <div class="row">
            <div class="col-md-12" >

                <div v-if="$gate.isAdmin()" class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Tests in Course {{coursename}}</h3>

                        <div class="card-tools">
                            <button class="btn btn-primary" @click="newModal">Add Test <i class="fas fa-book-open"></i></button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Description</th>
                                <th>Duration</th>
                                <th>Added At</th>
                                <th>Modify</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="test in tests.data" :key="test.id">
                                <td>{{test.id}}</td>
                                <td>{{test.name}}</td>
                                <td>{{test.course.name}}</td>
                                <td>{{test.description}}</td>
                                <td>{{test.duration}}</td>
                                <td>{{test.created_at | myDate}}</td>
                                <td>
                                    <a href="#" @click="editModal(test)">
                                        <i class="fa fa-edit orange"></i>
                                    </a>
                                    /
                                    <a href="#" @click="deleteTest(test.id)">
                                        <i class="fa fa-trash red"></i>
                                    </a>
                                    /
                                    <router-link :to="{ name: 'view_test', params: { id: test.id }}">
                                        <i class="fa fa-eye blue"></i>
                                    </router-link>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <pagination :data="tests" @pagination-change-page="getTestResults"></pagination>
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
                        <h5 v-show="editMode" class="modal-title">Update Test's Info</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="editMode ? updateTest() : createTest()">
                        <div class="modal-body">
                            <!--input edit form -->

                            <!--<input type="hidden" :value="$route.params.id" name="course_id"/>-->
                            <div class="form-group">
                                <input v-model="form.name" type="text" name="name"
                                       placeholder="Full Test Name"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                <has-error :form="form" field="name"></has-error>
                            </div>

                            <div class="form-group">
                                <textarea v-model="form.description" name="description"
                                          placeholder="Test Description"
                                          class="form-control" :class="{ 'is-invalid': form.errors.has('description') }"></textarea>
                                <has-error :form="form" field="description"></has-error>
                            </div>

                            <div class="form-group">
                                <input v-model="form.duration" type="text" name="duration"
                                       placeholder="Test Duration"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('duration') }">
                                <has-error :form="form" field="duration"></has-error>
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
                coursename: '',
                description: '',
                image: '',
                tests:{},
                form : new Form({
                    course_id: this.$route.params.id,
                    id:'',
                    name: '',
                    description: '',
                    duration:'',
                })
             }
        },
        methods: {
            getTestResults(page=1){
                axios.get('/api/tests?page='+page)
                    .then(response => {
                        this.tests = response.data;
                    })
            },
            createTest(){
                this.$Progress.start();
                this.form.post('/api/tests')
                    .then(() => {
                        //fire an event
                        Fire.$emit('TestCreated');

                        $('#addNew').modal('hide');

                        toast.fire({
                            icon: 'success',
                            title: 'Test created successfully'
                        });

                        this.$Progress.finish();
                    })
                    .catch( () => {
                        this.$Progress.fail();
                    });
            },
            updateTest(){
                this.$Progress.start();
                this.form.put('/api/tests/'+this.form.id)
                    .then(() => {
                        //fire an event
                        Fire.$emit('TestUpdated');

                        $('#addNew').modal('hide');

                        toast.fire({
                            icon: 'success',
                            title: 'Test updated successfully'
                        });

                        this.$Progress.finish();
                    })
                    .catch( () => {
                        this.$Progress.fail();
                    });
            },
            loadTests(){
                if (this.$gate.isAdmin() || this.$gate.isAuthor()){
                    axios.get("/api/tests/course/"+this.$route.params.id)
                        .then(({ data }) => {
                            this.tests = data;
                        });

                }

            },
            deleteTest(id){
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
                        this.form.delete('/api/tests/'+id)
                            .then(() => {
                                swal.fire(
                                    'Deleted!',
                                    'Test has been deleted.',
                                    'success'
                                );
                                Fire.$emit('TestDeleted');
                            })
                            .catch(() => {
                                swal.fire("Failed","There was something wrong.","warning");
                            })
                    }

                });

            },
            editModal(test){
                this.editMode = true;
                this.form.reset();
                $('#addNew').modal('show');
                this.form.fill(test);
            },
            newModal(){
                this.editMode = false;
                this.form.reset();
                $('#addNew').modal('show')
            },
            loadCourseInfo(){
                axios.get("/api/courses/"+this.$route.params.id)
                    .then(({ data }) => {
                        let course = data;
                        this.coursename = course.name;
                        this.description = course.description;
                        this.duration = course.duration;
                    })
            }
        },
        created() {
            this.loadCourseInfo();
            this.loadTests();
            Fire.$on('TestCreated', () => {
                this.loadTests();
            });
            Fire.$on('TestDeleted', () => {
                this.loadTests();
            });
            Fire.$on('TestUpdated', () => {
                this.loadTests();
            });
            Fire.$on('Searching', () => {
                let query = this.$parent.search;
                axios.get("/api/findTest?q="+query)
                    .then(({ data }) => {this.tests = data})
                    .catch(() => {

                    })
            });
            // setInterval(() => this.loadTests(),3000);
        }
    }
</script>
