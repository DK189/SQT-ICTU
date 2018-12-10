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
        $.ajaxSetup({
            beforeSend: function (xhr)
            {
               xhr.setRequestHeader("access-token",session["access-token"]);
            }
        });

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
		var tkb = {};

        if (Object.keys(w.time_table).length === 0) {
			var semesters = apiGet("semester").responseJSON;
			if (!!semesters) {
				semesters = semesters.filter(function (x) {return x.KyHienTai;});

				if (semesters.length > 0) {
					var semester = semesters[0];
					w.time_table[semester.MaKy] = {
						study: getTimeTableBySemester(semester.MaKy).responseJSON,
						exam: getExamTableBySemester(semester.MaKy).responseJSON,
					};
					console.log(semester);
				} else {
					alert(
						"Không có thông tin về kỳ hiện tại, vui lòng mở\n"
						+ "\tMenu > Đồng bộ dữ liệu\n"
						+ "Sau đó lựa chọn kỳ học cần đồng bộ lịch."
					);
				}

				session["time-table"] = JSON.stringify(w.time_table);
			} else {
				alert("Không thể lấy thông tin niên khoá của tài khoản này, vui lòng thử lại.");
				logout();
			}
        }

        console.log(w.time_table);

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
			},
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

    } else {
        $("#loginModal").modal();
        $("#loginModalButtonLogin").bind("click", function (e) {
            console.log(e);
            login(
                document.querySelector('#loginTxtUsername').value,
                document.querySelector('#loginTxtPassword').value
            );
        });
    }
})(2,window,document,jQuery);
