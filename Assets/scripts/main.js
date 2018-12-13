function apiGet (action, callback, forceSync) {
	return $.getJSON({
		url: "/api.php?action=" + action,
		async: !!callback && !forceSync
	}).always(callback || function () {});
}
function apiPost (action, data, callback, forceSync) {
	return $.getJSON({
		url: "/api.php?action=" + action,
		type: "post",
		data: data,
		async: !!callback && !forceSync
	}).always(callback || function () {});
}

document.body.removeAttribute("hidden");

Array.from(document.querySelectorAll("a[href='']")).forEach(function (a) {
	a.href = "javascript:void(0);";
});

(function (__APP_VESION__,w,d,$) {
	if (location.protocol.toUpperCase().indexOf("HTTPS") === -1) {
		// location.protocol = "https";
		// return;
	}
    var session = w.localStorage;

    function login (username, password) {
        $("button,input").attr("disabled", "disabled")
		apiPost("login", {username: username, password: password}, function (data, state) {
			if (state === "success" && !!data && (data + "").length > 0) {
                session["access-token"] = data;
				session["version"] = __APP_VESION__;
				location.reload();
            } else {
				alert("Không hợp lệ");
				$("button,input").removeAttr("disabled");
			}
		});
    }

    function logout () {
        session.clear();
        location.reload();
    }

    function getTimeTableBySemester (semester, callback) {
        return apiPost("time-table", {semester: semester}, callback);
    }

	function getExamTableBySemester (semester, callback) {
        return apiPost("exam-table", {semester: semester}, callback);
    }

    if (!!session["access-token"]) {
		if (session["version"] < __APP_VESION__) {
			alert("Phiên bản mới yêu cầu cập nhật lại thông tin. Xin lỗi về sự bất tiện này.");
			logout();
			return;
		}
        setTimeout(function () {
			$("#waitingModal").modal().one("shown.bs.modal", function () {
				$.ajaxSetup({
		            beforeSend: function (xhr)
		            {
		               xhr.setRequestHeader("access-token",session["access-token"]);
		            }
		        });

				function getTimeTable (semesters) {
					var table = {};
					if (!!semesters) {
						Array.from(semesters).forEach(function (semester) {
							table[semester.MaKy] = {
								study: getTimeTableBySemester(semester.MaKy).responseJSON,
								exam: getExamTableBySemester(semester.MaKy).responseJSON,
							};
						});
					}
					return table;
				}

		        if (!session["profile"]) {
					apiGet("profile", function (data) {
						if (!!data) {
		                    w.profile = data;
		                    session["profile"] = JSON.stringify(w.profile);
		                } else {
							alert("Your account have error.");
			                logout();
						}
					}, true);
		        } else {
		            w.profile = JSON.parse(session["profile"]);
		        }

				console.log(w.profile);

		        $("#userInfor_Name").text(w.profile.HoTen);
		        $("#userInfor_Class").text(w.profile.Lop);

				w.time_table = JSON.parse(session["time-table"] || "{}");
				w.semesters = JSON.parse(session["semesters"] || "{}");
				var tkb = {};

		        if (!w.semesters || Array.from(w.semesters).length === 0) {
					w.semesters = apiGet("semester").responseJSON;
					if (!!w.semesters) {
						session["semesters"] = JSON.stringify(w.semesters);
					} else {
						alert("Không thể lấy thông tin niên khoá của tài khoản này, vui lòng thử lại.");
						logout();
						return;
					}
		        }

				if (!w.time_table || Object.keys(w.time_table).length === 0) {
					var semesters = w.semesters;
					semesters = semesters.filter(function (x) {return x.KyHienTai;});

					if (semesters.length > 0) {
						w.time_table = getTimeTable(semesters);
					} else {
						alert(
							"Không có thông tin về kỳ hiện tại, vui lòng mở\n"
							+ "\tMenu > Đồng bộ dữ liệu\n"
							+ "Sau đó lựa chọn kỳ học cần đồng bộ lịch."
						);
					}

					session["time-table"] = JSON.stringify(w.time_table);
				}

		        console.log(w.semesters);
		        console.log(w.time_table);

				function refreshSyncModal (override) {
					var semestersLoaded = Object.keys(w.time_table);
					var lst = $("#listSemester");
					if (!!override) {
						$(lst).find("li").remove();
					}
					Array.from(w.semesters).forEach(function (semester) {
						if (!!semester.MaKy && !!semester.TenKy) {
							var $semester = $(lst).find("li#semester_" + semester.MaKy);
							if ($semester.length === 0) {
								$semester = $('<li class="mdl-list__item" id="semester_' + semester.MaKy + '">')
									.append(
										$('<span class="mdl-list__item-primary-content">')
											.append(
												$('<i class="material-icons  mdl-list__item-avatar">date_range</i>')
													.css({
														"border-radius" : "0",
														"background" : "inherit",
														"color" : "inherit",
													})
											)
											.append($('<span>').text(semester.TenKy))
									)
									.append(
										$('<span class="mdl-list__item-secondary-action">')
											.append(
												$('<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="toggle_semester_' + semester.MaKy + '">')
													.append(
														$('<input type="checkbox" id="toggle_semester_' + semester.MaKy + '" class="mdl-switch__input" />')
															.data("semester", semester)
													)
											)
									);
								$(lst).append($semester);
							}
							componentHandler.upgradeDom();

							var $switch = $($semester).find("label").prop("MaterialSwitch");
							if (!!$switch) {
								if (semestersLoaded.indexOf(semester.MaKy) > -1) {
									$switch.on();
								} else {
									$switch.off();
								}
							}
						}
					});
				}
				refreshSyncModal();

				$("#syncModalButtonReloadSemesters").bind("click", function (e) {
					$("#waitingModal").modal().one("shown.bs.modal", function () {

						var semesters = apiGet("semester").responseJSON;
						if (!!semesters) {
							w.semesters = semesters;
							session["semesters"] = JSON.stringify(w.semesters);
							refreshSyncModal(true);
						} else {
							alert("Không thể lấy thông tin niên khoá của tài khoản này, vui lòng thử lại.");
						}

						$("#waitingModal").modal("hide");
					});
				});

				$("#syncModalButtonSave").bind("click", function (e) {
					$(".modal").modal("hide");
					$("#waitingModal").modal().one("shown.bs.modal", function () {
						var semestersLoaded = Object.keys(w.time_table);
						var lst = $("#listSemester " + 'input[type="checkbox"]');
						var semesters = [];
						$(lst).each(function (k, x) {
							var semester = $(x).data("semester");
							if (!!$(x).prop("checked") && semestersLoaded.indexOf(semester.MaKy) === -1) {
								semesters.push(semester);
							}
							if (!$(x).prop("checked") && !!w.time_table[semester.MaKy]) {
								delete w.time_table[semester.MaKy];
							}
						});
						var table = getTimeTable(semesters);
						Object.keys(table).forEach(function (key) {
							w.time_table[key] = table[key];
						})
						session["time-table"] = JSON.stringify(w.time_table);
						location.reload();
					});
				});
				$("#syncModalButtonForceSync").bind("click", function (e) {
					$(".modal").modal("hide");
					$("#waitingModal").modal().one("shown.bs.modal", function () {
						var semestersLoaded = Object.keys(w.time_table);
						var lst = $("#listSemester " + 'input[type="checkbox"]');
						var semesters = [];
						$(lst).each(function (k, x) {
							var semester = $(x).data("semester");
							if (!!$(x).prop("checked")) {
								semesters.push(semester);
							}
						});
						w.time_table = getTimeTable(semesters);
						session["time-table"] = JSON.stringify(w.time_table);
						location.reload();
					});
				});

		        $("#calendar").fullCalendar({
		            locale: 'vi',
					defaultView: 'month',
					header: {
						center: 'month,listWeek' // buttons for switching between views
					},
					views: {
						month: {
							titleFormat: 'MM-YYYY'
						},
						listWeek: {
							titleFormat: 'DD-MM-YYYY'
						}
					}
		        });

				$(function ($) {
					$("#calendar").fullCalendar("option", "height", "parent");
				});

		        Object.keys(w.time_table).forEach(function (semester) {
		            if (!!w.time_table[semester]) {
		                var events = {
							study: [],
							exam: []
						};

						Object.keys(w.time_table[semester]).forEach(function (tbKey) {
							var tb = w.time_table[semester][tbKey];
							var subjectMap = {};
			                Array.from(tb.Subjects).forEach(function (s) {
			                    subjectMap[s.MaMon] = s;
			                });
			                Array.from(tb.Entries).forEach(function (t) {
			                    var s = subjectMap[t.MaMon];
			                    events[tbKey].push({
			                        title: s.TenMon + " (" + t.DiaDiem + " - " + t.ThoiGian + ")",
			                        start: moment(t.Ngay, "YYYY-MM-DD").toDate(),
			                        tag: {
			                            subject: s,
			                            table: t
			                        }
			                    });
			                });
						});

		                $("#calendar").fullCalendar(
		                    "addEventSource",
		                    {
		                        id: semester,
		                        events: events["study"],
		                        color: '#13A89E',
		                        textColor: '#ecf0f1'
		                    }
		                );

		                $("#calendar").fullCalendar(
		                    "addEventSource",
		                    {
		                        id: semester,
		                        events: events["exam"],
		                        color: '#9C27B0',
		                        textColor: '#f1ecf1'
		                    }
		                );
		            }
		        });

				$("#calendar").fullCalendar("getCalendar").on(
					"eventClick",
					function (dayEvent) {
						alert(dayEvent.title);
						console.log(arguments);
					}
				);

				$("#calendar").fullCalendar("render");

		        $("#buttonLogout").bind("click", function (e) {
		            logout();
		        });

		        $("#main").removeAttr("hidden");


			    window.addEventListener("beforeinstallprompt", function (e) {
			        e.preventDefault();
			        console.log(e);
			        $("#installModalSayYes").bind("click", function (me) {
			            console.log(e.prompt(), e);
			        });
			        $("#installModal").modal();
			    });

				$("#waitingModal").modal("hide");
			});
		}, 100);
    } else {
        $("#loginModal").modal();
		$("#loginTxtUsername").bind("keyup", function (e) {
			if (e.keyCode === 13) {
				$("#loginTxtPassword").focus();
			}
		});
		$("#loginTxtPassword").bind("keyup", function (e) {
			if (e.keyCode === 13) {
				login(
					$('#loginTxtUsername').val(),
					$('#loginTxtPassword').val()
				);
			}
		});
        $("#loginModalButtonLogin").bind("click", function (e) {
            login(
                document.querySelector('#loginTxtUsername').value,
                document.querySelector('#loginTxtPassword').value
            );
        });
    }
})(3,window,document,jQuery);
