<template>
    <div class="container">
        <div class="row">
            <div class="col-md-12" >

                <div v-if="$gate.isAdmin()" class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Courses</h3>

                        <div class="card-tools">
                            <button class="btn btn-primary" @click="newModal">Add New <i class="fas fa-book-open"></i></button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Image</th>
                                <th>Added At</th>
                                <th>Modify</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="course in courses.data" :key="course.id">
                                <td>{{course.id}}</td>
                                <td>{{course.name}}</td>
                                <td>{{course.description}}</td>
                                <td><img :src=" './img/course/'+course.image" width="100px" height="100px" /></td>
                                <td>{{course.created_at | myDate}}</td>
                                <td>
                                    <a href="#" @click="editModal(course)">
                                        <i class="fa fa-edit orange"></i>
                                    </a>
                                    /
                                    <a href="#" @click="deleteCourse(course.id)">
                                        <i class="fa fa-trash red"></i>
                                    </a>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <pagination :data="courses" @pagination-change-page="getResults"></pagination>
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
                        <h5 v-show="editMode" class="modal-title">Update Course's Info</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="editMode ? updateCourse() : createCourse()">
                        <div class="modal-body">
                            <!--input edit form -->

                            <div class="form-group">
                                <input v-model="form.name" type="text" name="name"
                                       placeholder="Full Course Name"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                <has-error :form="form" field="name"></has-error>
                            </div>

                            <div class="form-group">
                                <textarea v-model="form.description" name="description"
                                       placeholder="Course Description"
                                          class="form-control" :class="{ 'is-invalid': form.errors.has('description') }"></textarea>
                                <has-error :form="form" field="description"></has-error>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Course Image</label>
                                <input  type="file" name="image"
                                        @change="updateCoursePhoto"
                                        class="form-control" :class="{ 'is-invalid': form.errors.has('image') }">
                                <has-error :form="form" field="image"></has-error>
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
                courses: {},
                form : new Form({
                    id:'',
                    name: '',
                    description: '',
                    image:''
                })
             }
        },
        methods: {
            updateCoursePhoto(e){
                let file = e.target.files[0];
                let reader = new FileReader();

                if (file['size'] < 2111775){
                    reader.onloadend = (file) => {
                        this.form.image = reader.result;
                    };
                    reader.readAsDataURL(file);
                }
                else{
                    swal.fire({
                        type: 'error',
                        title: 'Oops...',
                        text:'You are uploading a file greater than 2 mb'
                    })
                }

            },
            getResults(page=1){
                axios.get('api/courses?page='+page)
                    .then(response => {
                        this.courses = response.data;
                    })
            },
            createCourse(){
                this.$Progress.start();
                this.form.post('api/courses')
                    .then(() => {
                        //fire an event
                        Fire.$emit('CourseCreated');

                        $('#addNew').modal('hide');

                        toast.fire({
                            icon: 'success',
                            title: 'Course created successfully'
                        });

                        this.$Progress.finish();
                    })
                    .catch( () => {
                        this.$Progress.fail();
                    });
            },
            updateCourse(){
                this.$Progress.start();
                this.form.put('api/courses/'+this.form.id)
                    .then(() => {
                        //fire an event
                        Fire.$emit('CourseUpdated');

                        $('#addNew').modal('hide');

                        toast.fire({
                            icon: 'success',
                            title: 'Course updated successfully'
                        });

                        this.$Progress.finish();
                    })
                    .catch( () => {
                        this.$Progress.fail();
                    });
            },
            loadCourses(){
                if (this.$gate.isAdmin() || this.$gate.isAuthor()){
                    axios.get("api/courses")
                        .then(({ data }) => {this.courses = data})
                }

            },
            deleteCourse(id){
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
                        this.form.delete('api/courses/'+id)
                            .then(() => {
                                swal.fire(
                                    'Deleted!',
                                    'Course has been deleted.',
                                    'success'
                                );
                                Fire.$emit('CourseDeleted');
                            })
                            .catch(() => {
                                swal.fire("Failed","There was something wrong.","warning");
                            })
                    }

                });

            },
            editModal(course){
                this.editMode = true;
                this.form.reset();
                $('#addNew').modal('show');
                this.form.fill(course);
            },
            newModal(){
                this.editMode = false;
                this.form.reset();
                $('#addNew').modal('show')
            }
        },
        created() {
            this.loadCourses();
            Fire.$on('CourseCreated', () => {
                this.loadCourses();
            });
            Fire.$on('CourseDeleted', () => {
                this.loadCourses();
            });
            Fire.$on('CourseUpdated', () => {
                this.loadCourses();
            });
            Fire.$on('Searching', () => {
                let query = this.$parent.search;
                axios.get("api/findCourse?q="+query)
                    .then(({ data }) => {this.courses = data})
                    .catch(() => {

                    })
            });
            // setInterval(() => this.loadCourses(),3000);
        }
    }
</script>
