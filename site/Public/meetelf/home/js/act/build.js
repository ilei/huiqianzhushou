({
    appDir: './',
    baseUrl: './',
    dir:'./out',
    mainConfigFile: '../../../../common/js/require.config.js',
    fileExclusionRegExp: /^(r|build|)\.js$/,
    skipDirOptimize:true,
    modules: 
    [
     {
       name:'home.act.add',
       exclude:['jquery', 'bootstrap'],
     },
     {
       name:'home.act.tmanage',
       exclude:['jquery', 'bootstrap'],
     },
    ],
    optimize: "uglify",
})
