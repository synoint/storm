({
    name: "synoES_Cookie",
    set: function (n, v, c) {
        if (this.shouldSendSameSiteNone()) {
            this.setSecure(n, v, c);
        } else {
            this.setNonSecure(n, v, c);
        }
    },
    setNonSecure: function (n, v, c) {
        var d, e = "";
        d = new Date();
        if (c) {
            d.setTime(d.getTime() + c * 60 * 60 * 1000);
        }
        e = "; expires=" + d.toGMTString();
        document.cookie = escape(n) + "=" + v + e + "; path=/";
    },
    setSecure: function (n, v, c) {
        var d, e = "";
        d = new Date();
        if (c) {
            d.setTime(d.getTime() + c * 60 * 60 * 1000);
        }
        e = "; expires=" + d.toGMTString();
        document.cookie = escape(n) + "=" + v + e + "; path=/; SameSite=None; Secure";
    },
    get: function (n) {
        var e, b, p, c = document.cookie;
        p = n + "=";
        b = c.indexOf(";" + " " + p);
        if (b === -1) {
            b = c.indexOf(p);
            if (b !== 0) {
                return "";
            }
        } else {
            b += 2;
        }
        e = c.indexOf(";", b);
        if (e === -1) {
            e = c.length;
        }
        return unescape(c.substring(b + p.length, e));
    },
    unset: function (n) {
        return this.set(n, "");
    },
    init: function () {
        window[this.name] = this;
    },
    shouldSendSameSiteNone: function () {
        return !this.isSameSiteNoneIncompatible(navigator.userAgent);
    },
    isSameSiteNoneIncompatible: function (useragent) {
        return this.hasWebKitSameSiteBug(useragent) || this.dropsUnrecognizedSameSiteCookies(useragent);
    },
    hasWebKitSameSiteBug: function (useragent) {
        return this.isIosVersion(12, useragent) ||
            (this.isMacosxVersion(10, 14, useragent) && (this.isSafari(useragent) || this.isMacEmbeddedBrowser(useragent)));
    },
    dropsUnrecognizedSameSiteCookies: function (useragent) {
        if (this.isUcBrowser(useragent)) {
            return !this.isUcBrowserVersionAtLeast(12, 13, 2, useragent);
        }
        return this.isChromiumBased(useragent) &&
            this.isChromiumVersionAtLeast(51, useragent) &&
            !this.isChromiumVersionAtLeast(67, useragent);
    },
    isIosVersion: function (major, useragent) {
        var match = useragent.match(/iP.+;\sCPU\s.*OS\s(\d+)[_\d]*.*AppleWebKit/i);
        return match && match[1] === major;
    },
    isMacosxVersion: function (major, minor, useragent) {
        var match = useragent.match(/Macintosh;\s.*Mac\sOS\sX\s(\d+)_(\d+)[_\d+]*.*AppleWebKit/i);
        return match && match.length > 2 && match[1] === major && match[2] === minor;
    },
    isSafari: function (useragent) {
        var match = useragent.match(/Version.*Safari/i);
        return match && !isChromiumBased(useragent)
    },
    isMacEmbeddedBrowser: function (useragent) {
        var match = useragent.match(/^Mozilla\/[.\d]+.*\(Macintosh;.*Mac OS X\s*[_\d]?.*\) AppleWebKit\/[.\d]+.*\(KHTML, like Gecko.*\)/i);
        return match && match.length > 0;
    },
    isChromiumBased: function (useragent) {
        var match = useragent.match(/Chrom[[e|ium]+/i);
        return match && match.length > 0;
    },
    isChromiumVersionAtLeast: function (major, useragent) {
        var match = useragent.match(/Chrom[^ \/]+\/(\d+)[.\d]*/i);
        return match && match.length > 0 && match[0] >= major;
    },
    isUcBrowser: function (useragent) {
        var match = useragent.match(/UCBrowser/i);
        return match && match.length > 0;
    },
    isUcBrowserVersionAtLeast: function (major, minor, build, useragent) {
        var match = useragent.match(/UCBrowser\/(\d+)\.(\d+)\.(\d+)[.\d]*/i);
        if (match && match.length >= 4) {
            var major_version = parseInt(match[1]);
            var minor_version = parseInt(match[2]);
            var build_version = parseInt(match[3]);
            if (major_version !== major) {
                return major_version > major;
            }
            if (minor_version !== minor) {
                return minor_version > minor;
            }
            return build_version >= build;
        }
    }
}).init();

if (!window.synoES) {
    var head = document.head;
    var link = document.createElement("link");

    link.type = "text/css";
    link.rel = "stylesheet";
    link.href = 'https://survey.synointcdn.com/embed/v1/style.min.css';

    head.appendChild(link);

    // Seems to be a Safari bug. All properties of location are undefined
    try {
        window.loc_ = (window.location.href === 'undefined' && JSON && JSON.parse && JSON.stringify) ? JSON.parse(JSON.stringify(window.location)) : window.location;
    } catch (err) {
        window.loc_ = window.location;
    }
    if (!window.loc_.origin) {
        window.loc_.origin = window.loc_.protocol + "//" + window.loc_.hostname + (window.loc_.port ? ':' + window.loc_.port : '');
    }
    window.synoES = window.synoES || {};
    synoES.survey = synoES.survey || {};

    var synoES_SETTINGS = {
        CONTAINER_ID: "",
        SESSION_DURATION: 55,//seconds
        INVITATION_TIMEOUT: 0,//seconds
        SESSION_START_COOKIE: 'es_session_start',
        SESSION_ALIVE_COOKIE: 'es_session_last',
        SURVEY_POOL_COOKIE: 'es_pool'
    }

    function InvitationTimeoutHandler(settings) {
        this.settings = settings;

        function init() {
            var _this = this;
            started_ = new Date().getTime();
            run_(_this.settings);
        }

        this["init"] = init;

        function finished() {
            return !running;
        }

        this["finished"] = finished;

        function run_(settings) {
            running = 1;

            var sessionStart = synoES_Cookie.get(synoES_SETTINGS.SESSION_START_COOKIE);
            var lastSessionActivity = synoES_Cookie.get(synoES_SETTINGS.SESSION_ALIVE_COOKIE);
            var now = new Date().getTime();
            //first time visit on site
            if (!sessionStart) {
                sessionStart = now;
                synoES_Cookie.set(synoES_SETTINGS.SESSION_START_COOKIE, sessionStart, 1);
            }
            if (!lastSessionActivity) {
                lastSessionActivity = now;
            }

            //if more than max session duration passed since last site visit
            if ((now - lastSessionActivity) / 1000 > synoES_SETTINGS.SESSION_DURATION) {
                sessionStart = now;
                synoES_Cookie.set(synoES_SETTINGS.SESSION_START_COOKIE, sessionStart, 1);
            }

            var sessionDuration = (lastSessionActivity - sessionStart) / 1000;
            if (sessionDuration > synoES_SETTINGS.INVITATION_TIMEOUT) {
                synoES.survey.show(settings);
                return;
            }

            synoES_Cookie.set(synoES_SETTINGS.SESSION_ALIVE_COOKIE, now, 1);

            window.setTimeout(function () {
                run_(settings);
            }, timeout_);

        }

        var timeout_ = 1000;
        var started_ = new Date().getTime();
        var running = 0;
    }

    synoES.init = function (settings) {
        if (settings['skipURLs'] && settings['skipURLs'].length > 0) {
            for (var index in settings.skipURLs) {
                if (settings.skipURLs[index] === document.location.href) {
                    return;
                }
            }
        }


        synoES_SETTINGS.CONTAINER_ID = settings.containerId;
        synoES_SETTINGS.INVITATION_TIMEOUT = settings.invitationTimeoutSeconds || 0;
        settings.titleText = settings.titleText || 'Title (titleText)!';
        settings.hintText = settings.hintText || 'hintText';
        settings.buttonLabel = settings.buttonLabel || 'Next';
        settings.buttonColor = settings.buttonColor || 'rgb(224, 104, 145)';
        settings.containerColor = settings.containerColor || 'rgb(51, 51, 51)';

        if (settings['surveyPoolSizePercent']) {
            var isInPool = synoES_Cookie.get(synoES_SETTINGS.SURVEY_POOL_COOKIE);
            if (!isInPool) {
                var poolExpirationHours = (parseInt(settings['surveyPoolExpirationDays']) || 30) * 24;
                isInPool = Math.random() * 100 > parseInt(settings['surveyPoolSizePercent']) ? 'n' : 'y';
                synoES_Cookie.set(synoES_SETTINGS.SURVEY_POOL_COOKIE, isInPool, poolExpirationHours);
            }
            if ('n' === isInPool) {
                return;
            }
        }

        if (settings['invitationTimeoutSeconds']) {
            synoES.invitationTimeoutHandler = new InvitationTimeoutHandler(settings);
            synoES.invitationTimeoutHandler.init();
        } else {
            synoES.survey.show(settings);
        }

    };

    synoES.survey.show = function (settings) {
        var rootElement = document.getElementById(synoES_SETTINGS.CONTAINER_ID);
        rootElement.innerHTML = synoES.survey.getSurveyTemplate(settings);
    }

    synoES.survey.closeInvitationPopup = function () {
        var rootElement = document.getElementById(synoES_SETTINGS.CONTAINER_ID);
        rootElement.parentNode.removeChild(rootElement);
    };

    synoES.survey.updateInvitationPopupVisibility = function () {
        var containerStyle = document.querySelector('.sss-invitation-container').style;
        var expandCollapseButtonStyle = document.querySelector('.sss-collapse-button-icon').style;
        if (containerStyle.transform) {
            containerStyle.removeProperty('transform');
            expandCollapseButtonStyle.backgroundPosition = '-32px 0';
            document.querySelector('.sss-invitation-container').style.marginBottom = '0px';
        } else {
            document.querySelector('.sss-invitation-container').style.transform = "translateY(100%)";
            document.querySelector('.sss-invitation-container').style.marginBottom = '8px';
            expandCollapseButtonStyle.backgroundPosition = '0 0';
        }
    };

    synoES.survey.loadSurvey = function (synoSurveyURL) {
        document.querySelector('.sss-invitation-form').style.display = 'none';

        var surveyIframeContainer = document.querySelector('.sss-survey-iframe-container');
        surveyIframeContainer.style.display = 'block';

        var surveyIframe = document.createElement('IFRAME');
        surveyIframe.width = '100%';
        surveyIframe.height = '600px';
        surveyIframe.frameBorder = 0;
        surveyIframe.src = synoSurveyURL;
        surveyIframeContainer.appendChild(surveyIframe);
    };

    synoES.survey.getSurveyTemplate = function (settings) {
        var templateChunks = [
            '<div class="sss-survey-container">',
            '<div class="sss-invitation-container" style="background: ' + settings.containerColor + ';">',
            '<button class="sss-collapse-button" style="background: ' + settings.containerColor + ';" ',
            'onclick="return synoES.survey.updateInvitationPopupVisibility();"><span class="sss-collapse-button-icon"></span></button>',
            '<button class="sss-close-button" style="background: ' + settings.containerColor + ';" ',
            'onclick="return synoES.survey.closeInvitationPopup();"><span class="sss-close-button-icon">x</span></button>',
            '<form class="sss-invitation-form">',
            '<div class="sss-info-text">' + settings.titleText + '</div>',
            '<div class="sss-info-text-hint">' + settings.hintText + '</div>',
            '<div class="sss-action-container">',
            '<div style="float: right !important;">',
            '<button type="button" onclick="synoES.survey.loadSurvey(\'' + settings.surveyURL + '\');" style="background-color: ' + settings.buttonColor + ' !important;" class="sss-action-button">',
            settings.buttonLabel + '<span class="sss-action-button-icon"></span></button>',
            '</div>',
            '</div>',
            '</form>',
            '<div class="sss-survey-iframe-container">',
            '</div>',
            '</div>',
            '</div>',
        ];

        return templateChunks.join('');
    };
}