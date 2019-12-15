<style>
    .widget-user-header{
        background-position: center center;
        background-size: cover;
        height:250px !important;
    }
</style>

<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 mt-3">
                <div class="card card-widget widget-user">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header text-white" style="background: url('./img/cover.jpg') center center;">
                        <h3 class="widget-user-username text-right">{{this.form.name}}</h3>
                        <h5 class="widget-user-desc text-right">{{this.form.type}}</h5>
                    </div>
                    <div class="widget-user-image">
                        <img class="img-circle" :src="profilephoto" alt="User Avatar">
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">3,200</h5>
                                    <span class="description-text">SALES</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">13,000</h5>
                                    <span class="description-text">FOLLOWERS</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4">
                                <div class="description-block">
                                    <h5 class="description-header">35</h5>
                                    <span class="description-text">PRODUCTS</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Activity</a></li>
                            <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a></li>
                        </ul>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="active tab-pane" id="activity">
                               <h2>User Activity</h2>
                            </div>
                            <!-- /.tab-pane -->

                            <div class="tab-pane" id="settings">
                                <!--input edit form -->

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Full Name</label>
                                        <input v-model="form.name" type="text" name="name"
                                               placeholder="Full Name"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                        <has-error :form="form" field="name"></has-error>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Email Address</label>
                                        <input v-model="form.email" type="email" name="email"
                                               placeholder="Email Address"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                                        <has-error :form="form" field="email"></has-error>
                                    </div>

                                    <div class="form-group col-md-6" v-if="$gate.isAdmin()">
                                        <label>User Type</label>
                                        <select v-model="form.type" name="type"
                                                class="form-control" :class="{ 'is-invalid': form.errors.has('type') }">
                                            <option value="">Select User Type</option>
                                            <option value="user">Standard User</option>
                                            <option value="admin">Administrator</option>
                                            <option value="author">Author</option>
                                        </select>
                                        <has-error :form="form" field="type"></has-error>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Bio</label>
                                        <textarea v-model="form.bio" name="bio"
                                                  placeholder="bio"
                                                  class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
                                        <has-error :form="form" field="bio"></has-error>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Photo</label>
                                        <input type="hidden" name="prev_photo" value="" />
                                        <input  type="file" name="photo"
                                                @change="updateProfilePhoto"
                                                class="form-control" :class="{ 'is-invalid': form.errors.has('photo') }">
                                        <has-error :form="form" field="photo"></has-error>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Password (Leave empty if you aren't changing it)</label>
                                        <input v-model="form.password" type="text" name="password"
                                               placeholder="Password"
                                               class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                                        <has-error :form="form" field="password"></has-error>
                                    </div>
                                </div>

                                <button type="submit" @click.prevent="updateUser" class="btn btn-warning">Update</button>
                            </div>
                            <!-- /.tab-pane -->
                        </div>
                        <!-- /.tab-content -->
                    </div><!-- /.card-body -->
                </div>
                <!-- /.nav-tabs-custom -->
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                profilephoto:'',
                form : new Form({
                    id:'',
                    name: '',
                    email: '',
                    password: '',
                    type: '',
                    bio:'',
                    photo:'',
                    prev_photo:''
                })
            }
        },
        methods:{
            updateProfilePhoto(e){
                let file = e.target.files[0];
                let reader = new FileReader();

                if (file['size'] < 2111775){
                    reader.onloadend = (file) => {
                        this.form.photo = reader.result;
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
            loadProfile(){
                axios.get("api/profile")
                    .then(({ data }) => {this.form.fill(data)})
                    .then(() => {
                        this.$parent.userimage = this.profilephoto = "img/profile/" + this.form.photo;
                        this.$parent.username = this.form.name;
                        this.$parent.usertype = this.form.type;
                    })
            },

            updateUser(){
                this.$Progress.start();
                this.form.put('api/profile')
                    .then(() => {
                        //fire an event
                        Fire.$emit('LoadProfile');

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
        },
        created() {
            this.loadProfile();
            Fire.$on('LoadProfile', () => {
                this.loadProfile();
            });
        }
    }
</script>
