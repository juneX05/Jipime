const courses_routes = [
    {name:'view_courses',path: '/courses', component:require('./components/Courses.vue').default},
    {name:'view_course',path: '/course/view/:id', component:require('./components/CourseInfo.vue').default},
    {name:'view_test',path: '/test/view/:id', component:require('./components/TestInfo.vue').default},
    {name:'preview_test',path: '/test/preview/:id', component:require('./components/TestPreview.vue').default},

];

export default courses_routes