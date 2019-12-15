<template>
    <div class="container">
        <div class="row">
            <div class="col-md-12" >

                <div v-if="$gate.isAdmin()||$gate.isAuthor()" class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Users</h3>

                        <div class="card-tools">
                            <button class="btn btn-primary" @click="newModal">Add New <i class="fas fa-user-plus"></i></button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Registerd At</th>
                                <th>Modify</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                <td>{{user.id}}</td>
                                <td>{{user.name}}</td>
                                <td>{{user.email}}</td>
                                <td>{{user.type | upText}}</td>
                                <td>{{user.created_at | myDate}}</td>
                                <td>
                                    <a href="#" @click="editModal(user)">
                                        <i class="fa fa-edit orange"></i>
                                    </a>
                                    /
                                    <a href="#" @click="deleteUser(user.id)">
                                        <i class="fa fa-trash red"></i>
                                    </a>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <pagination :data="users" @pagination-change-page="getResults"></pagination>
                    </div>
                </div>
                <div v-if="!($gate.isAdmin()||$gate.isAuthor())">
                    <not-found></not-found>
                </div>
                <!-- /.card -->
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" v-if="$gate.isAdmin()||$gate.isAuthor()" id="addNew" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 v-show="!editMode" class="modal-title">Add New</h5>
                        <h5 v-show="editMode" class="modal-title">Update User's Info</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="editMode ? updateUser() : createUser()">
                        <div class="modal-body">
                            <!--input edit form -->

                            <div class="form-group">
                                <input v-model="form.name" type="text" name="name"
                                       placeholder="Full Name"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                <has-error :form="form" field="name"></has-error>
                            </div>

                            <div class="form-group">
                                <input v-model="form.email" type="email" name="email"
                                       placeholder="Email Address"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                                <has-error :form="form" field="email"></has-error>
                            </div>



                            <div class="form-group">
                                <select v-model="form.type" name="type"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('type') }">
                                    <option value="">Select User Type</option>
                                    <option value="user">Standard User</option>
                                    <option value="admin">Administrator</option>
                                    <option value="author">Author</option>
                                </select>
                                <has-error :form="form" field="type"></has-error>
                            </div>

                            <div class="form-group">
                                <textarea v-model="form.bio" name="bio"
                                       placeholder="bio"
                                          class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
                                <has-error :form="form" field="bio"></has-error>
                            </div>

                            <div class="form-group">
                                <input v-model="form.photo" type="text" name="photo"
                                       placeholder="photo"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('photo') }">
                                <has-error :form="form" field="photo"></has-error>
                            </div>

                            <div class="form-group">
                                <input v-model="form.password" type="text" name="password"
                                       placeholder="Password"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                                <has-error :form="form" field="password"></has-error>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-dange" data-dismiss="modal">Close</button>
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
                users: {},
                form : new Form({
                    id:'',
                    name: '',
                    email: '',
                    password: '',
                    type: '',
                    bio:'',
                    photo:''
                })
             }
        },
        methods: {
            getResults(page=1){
                axios.get('api/users?page='+page)
                    .then(response => {
                        this.users = response.data;
                    })
            },
            createUser(){
                this.$Progress.start();
                this.form.post('api/users')
                    .then(() => {
                        //fire an event
                        Fire.$emit('UserCreated');

                        $('#addNew').modal('hide');

                        toast.fire({
                            icon: 'success',
                            title: 'User created successfully'
                        });

                        this.$Progress.finish();
                    })
                    .catch( () => {
                        this.$Progress.fail();
                    });
            },
            updateUser(){
                this.$Progress.start();
                this.form.put('api/users/'+this.form.id)
                    .then(() => {
                        //fire an event
                        Fire.$emit('UserUpdated');

                        $('#addNew').modal('hide');

                        toast.fire({
                            icon: 'success',
                            title: 'User updated successfully'
                        });

                        this.$Progress.finish();
                    })
                    .catch( () => {
                        this.$Progress.fail();
                    });
            },
            loadUsers(){
                if (this.$gate.isAdmin() || this.$gate.isAuthor()){
                    axios.get("api/users")
                        .then(({ data }) => {this.users = data})
                }

            },
            deleteUser(id){
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
                        this.form.delete('api/users/'+id)
                            .then(() => {
                                swal.fire(
                                    'Deleted!',
                                    'User has been deleted.',
                                    'success'
                                );
                                Fire.$emit('UserDeleted');
                            })
                            .catch(() => {
                                swal.fire("Failed","There was something wrong.","warning");
                            })
                    }

                });

            },
            editModal(user){
                this.editMode = true;
                this.form.reset();
                $('#addNew').modal('show');
                this.form.fill(user);
            },
            newModal(){
                this.editMode = false;
                this.form.reset();
                $('#addNew').modal('show')
            }
        },
        created() {
            this.loadUsers();
            Fire.$on('UserCreated', () => {
                this.loadUsers();
            });
            Fire.$on('UserDeleted', () => {
                this.loadUsers();
            });
            Fire.$on('UserUpdated', () => {
                this.loadUsers();
            });
            Fire.$on('Searching', () => {
                let query = this.$parent.search;
                axios.get("api/findUser?q="+query)
                    .then(({ data }) => {this.users = data})
                    .catch(() => {

                    })
            });
            // setInterval(() => this.loadUsers(),3000);
        }
    }
</script>
