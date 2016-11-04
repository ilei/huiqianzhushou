module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		requirejs: {
			build_mobile_view: {
				options: {
					baseUrl: '../mobile/js',
					mainConfigFile: '../mobile/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'view',
          include:['common'],
					out:'../mobile/js/min/view.js'
				}
			},
			build_mobile_common: {
				options: {
					baseUrl: '../mobile/js',
					mainConfigFile: '../mobile/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'common',
					out:'../mobile/js/min/common.js'
				}
			},
			build_mobile_findpwd: {
				options: {
					baseUrl: '../mobile/js',
					mainConfigFile: '../mobile/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
          include:['common'],
					name:'findpwd',
					out:'../mobile/js/min/findpwd.js'
				}
			},
			build_mobile_register: {
				options: {
					baseUrl: '../mobile/js',
					mainConfigFile: '../mobile/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
          include:['common'],
					name:'register',
					out:'../mobile/js/min/register.js'
				}
			},
			build_mobile_report: {
				options: {
					baseUrl: '../mobile/js',
					mainConfigFile: '../mobile/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
          include:['common'],
					name:'report',
					out:'../mobile/js/min/report.js'
				}
			},
			build_mobile_signup: {
				options: {
					baseUrl: '../mobile/js',
					mainConfigFile: '../mobile/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
          include:['common'],
					name:'signup',
					out:'../mobile/js/min/signup.js'
				}
			},
			build_mobile_login: {
				options: {
					baseUrl: '../mobile/js',
					mainConfigFile: '../mobile/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
          include:['common'],
					name:'login',
					out:'../mobile/js/min/login.js'
				}
			},
			build_hfsa: {
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.financing.set_account',
					out:'../meetelf/home/js/min/home.financing.set_account.js'
				}
			},
			build_hfuht: {
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.financing.underway_history_ticket',
					out:'../meetelf/home/js/min/home.financing.underway_history_ticket.js'
				}
			},
			build_hfae: {
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.financing.add_edit',
					out:'../meetelf/home/js/min/home.financing.add_edit.js'
				}
			},
			build_hfac: {
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.financing.all_choice',
					out:'../meetelf/home/js/min/home.financing.all_choice.js'
				}
			},
			build_hfi: {
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.financing.index',
					out:'../meetelf/home/js/min/home.financing.index.js'
				}
			},
			build_hfpl: {
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.financing.public_list',
					out:'../meetelf/home/js/min/home.financing.public_list.js'
				}
			},
			build_hfpt: {
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.financing.public_ticket',
					out:'../meetelf/home/js/min/home.financing.public_ticket.js'
				}
			},
			build_account: {
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'account',
					out:'../meetelf/home/js/min/account.js'
				}
			},
			build_birthday: {
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'birthday',
					out:'../meetelf/home/js/min/birthday.js'
				}
			},
			build_signup_userinfo:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'signup_userinfo',
					out:'../meetelf/home/js/min/signup_userinfo.js'
				}
			},
			build_homeevent:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.event',
					out:'../meetelf/home/js/min/home.event.js'
				}
			},
			build_formform_set:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'form.form_set',
					out:'../meetelf/home/js/min/form.form_set.js'
				}
			},
			build_popup:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'popup',
					out:'../meetelf/home/js/min/popup.js'
				}
			},
			build_feedback:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'feedback',
					out:'../meetelf/home/js/min/feedback.js'
				}
			},
			build_buybuy_email:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'buy.buy_email',
					out:'../meetelf/home/js/min/buy.buy_email.js'
				}
			},
			build_up_pwd:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'up_pwd',
					out:'../meetelf/home/js/min/up_pwd.js'
				}
			},
			build_find_pwd:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'find_pwd',
					out:'../meetelf/home/js/min/find_pwd.js'
				}
			},
			build_homeinformationorder:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.information.order',
					out:'../meetelf/home/js/min/home.information.order.js'
				}
			},
			build_actact_manage:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'act.act_manage',
					out:'../meetelf/home/js/min/act.act_manage.js'
				}
			},
			build_homeactadd:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.act.add',
					out:'../meetelf/home/js/min/home.act.add.js'
				}
			},
			build_homeacttmanage:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.act.tmanage',
					out:'../meetelf/home/js/min/home.act.tmanage.js'
				}
			},
			build_homeinformationchangemobilestep2:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.information.change.mobile.step2',
					out:'../meetelf/home/js/min/home.information.change.mobile.step2.js'
				}
			},	
			build_homeinformationchangemobile:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.information.change.mobile',
					out:'../meetelf/home/js/min/home.information.change.mobile.js'
				}
			},		
			build_organizer:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'organizer',
					out:'../meetelf/home/js/min/organizer.js'
				}
			},		
			build_homeinformation:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.information',
					out:'../meetelf/home/js/min/home.information.js'
				}
			},	
			build_homeindex_index:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.index.index',
					out:'../meetelf/home/js/min/home.index.index.js'
				}
			},		
			build_homeauthregister:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.auth.register',
					out:'../meetelf/home/js/min/home.auth.register.js'
				}
			},	
			build_homeauthlogin:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.auth.login',
					out:'../meetelf/home/js/min/home.auth.login.js'
				}
			},	
			build_homeinformationindex:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.information.index',
					out:'../meetelf/home/js/min/home.information.index.js'
				}
			},		
			build_homerelease:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.release',
					out:'../meetelf/home/js/min/home.release.js'
				}
			},		
			build_ticketmine_tickets:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'ticket.mine_tickets',
					out:'../meetelf/home/js/min/ticket.mine_tickets.js'
				}
			},	
			build_ordermine_orders:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'order.mine_orders',
					out:'../meetelf/home/js/min/order.mine_orders.js'
				}
			},		
			build_orderreview:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'order.review',
					out:'../meetelf/home/js/min/order.review.js'
				}
			},		
			build_homebasepassword:{
				options: {
					baseUrl: '../meetelf/home/js',
					mainConfigFile: '../common/js/require.config.js',
					fileExclusionRegExp: /^(r|build|)\.js$/,
					name:'home.base.password',
					out:'../meetelf/home/js/min/home.base.password.js'
				}
			}			
		},
    cssmin: {
      options: {
        beautify: {
          ascii_only: true
        }
      },
      build_common: {
        files: [
            { 
              expand: true,
              cwd: '../common/css/',
              src: '*.css',
              dest: '../common/css/',
              rename: function getName(dest, src) {  
                     var folder = src.substring(0, src.lastIndexOf('/'));  
                     var filename = src.substring(src.lastIndexOf('/'), src.length);  
                     filename = filename.substring(0, filename.lastIndexOf('.'));  
                     var fileresult=dest + folder + filename + '.min.css';  
                     grunt.log.writeln("现处理文件："+src+"  处理后文件："+fileresult);  
                     return fileresult;
              }
            }
        ]
      },
      build_home: {
        files: [
            { 
              expand: true,
              cwd: '../meetelf/css/',
              src: '*.css',
              dest: '../meetelf/css/',
              rename: function getName(dest, src) {  
                     var folder = src.substring(0, src.lastIndexOf('/'));  
                     var filename = src.substring(src.lastIndexOf('/'), src.length);  
                     filename = filename.substring(0, filename.lastIndexOf('.'));  
                     var fileresult=dest + folder + filename + '.min.css';  
                     grunt.log.writeln("现处理文件："+src+"  处理后文件："+fileresult);  
                     return fileresult;
              }
            }
        ]
      },
      build_meetelf: {
        files: [
            { 
              expand: true,
              cwd: '../meetelf/home/css/',
              src: '*.css',
              dest: '../meetelf/home/css/',
              rename: function getName(dest, src) {  
                     var folder = src.substring(0, src.lastIndexOf('/'));  
                     var filename = src.substring(src.lastIndexOf('/'), src.length);  
                     filename = filename.substring(0, filename.lastIndexOf('.'));  
                     var fileresult=dest + folder + filename + '.min.css';  
                     grunt.log.writeln("现处理文件："+src+"  处理后文件："+fileresult);  
                     return fileresult;
              }
            }
        ]
      },
      build_sign:{
        files:[
            {
             expand: true,
             cwd: '../signin/css/',
             src: '*.css',
             dest: '../signin/css/',
             rename: function getName(dest, src) {  
                     var folder = src.substring(0, src.lastIndexOf('/'));  
                     var filename = src.substring(src.lastIndexOf('/'), src.length);  
                     filename = filename.substring(0, filename.lastIndexOf('.'));  
                     var fileresult=dest + folder + filename + '.min.css';  
                     grunt.log.writeln("现处理文件："+src+"  处理后文件："+fileresult);  
                     return fileresult;
               }
             }
       ]
    },  
    build_mobile:{
      files:[
       { expand: true,
        cwd: '../mobile/css/',
        src: '*.css',
        dest: '../mobile/css/',
        rename: function getName(dest, src) {  
               var folder = src.substring(0, src.lastIndexOf('/'));  
               var filename = src.substring(src.lastIndexOf('/'), src.length);  
               filename = filename.substring(0, filename.lastIndexOf('.'));  
               var fileresult=dest + folder + filename + '.min.css';  
               grunt.log.writeln("现处理文件："+src+"  处理后文件："+fileresult);  
               return fileresult;        
          }
        }
      ]
    }
  }
});
	grunt.loadNpmTasks('grunt-contrib-requirejs');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.registerTask('default', ['requirejs','cssmin']);
}
