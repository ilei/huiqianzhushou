({
    appDir: '../../../common/js',
    baseUrl: '../../meetelf/home/js/',
    mainConfigFile: '../../../common/js/main-config.js',
    dir: './dist',
    modules: [
        {
            name: 'home.act.add'
        }
    ],
    fileExclusionRegExp: /^(r|build)\.js$/,
    removeCombined: true,
})
