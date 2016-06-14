<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

# 기존 http 규격에 따른 성공과 실패
const SUCCESS  = 200;
const ERR_FAIL = 400;

# 메시지, 파라미터 관련 실패 코드
const ERR_MSG_INVALID_VALUE = 1000;
const ERR_MSG_INVALID_CATEGORY_NAME = 1001;
const ERR_MSG_INVALID_SESSION_ID = 1002;
const ERR_MSG_INVALID_PARAMETER = 1003;
const ERR_MSG_INVALID_PRODUCT_IMAGE = 1004;
const ERR_MSG_INVALID_INGREDIENT_IMAGE = 1005;

# 로그인 관련 실패 코드 
const ERR_LOGIN_FAILED = 2000;
const ERR_LOGIN_FAILED_EMAIL = 2001;
const ERR_LOGIN_FAILED_PASSWORD = 2002;
const ERR_LOGIN_UNAUTHORIZED_EMAIL = 2003;

const ERR_JOIN_DUP_EMAIL = 2100;
const ERR_JOIN_DUP_NICKNAME = 2101;

# 데이터베이스 관련 실패 코드
const ERR_DB_NODATA = 3000;
const ERR_DB_DUPLICATION_DATA= 3001;
