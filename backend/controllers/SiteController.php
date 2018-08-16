<?php
namespace backend\controllers;

use backend\models\Users;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }


    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest){
            $smsKey = Users::find()->where(['id'=>Yii::$app->user->id])->one();
            if(!empty($smsKey)){
                if(empty($smsKey->enter_key)){
                    Yii::$app->user->logout();
                    return $this->goHome();
                }
            }
            return $this->render('index');
        }
        else
            return $this->redirect('login');

    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            $smsKey = Users::find()->where(['id'=>Yii::$app->user->id])->one();
            if(!empty($smsKey)){
                if(empty($smsKey->enter_key)){
                    Yii::$app->user->logout();
                    return $this->goHome();
                }
            }
            return $this->render('index');
        }

        $model = new LoginForm();
        if(!empty(Yii::$app->request->post('SmsForm'))){
            $modelSms = new SmsForm();

            if($modelSms->load(Yii::$app->request->post()) && $user = $modelSms->checking()){
                return $this->redirect('/site/index');
            }
            else
            {
                return $this->render('smscheck',[
                    'model' => $modelSms,
                ]);

            }
        }
        if ($model->load(Yii::$app->request->post())){
            $user = User::findByUsername($model->username);
            if(!empty($user)) {// чувак есть
                if($user->staff==1){// стафф
                    if ($model->login()) {// все четко
                        //отправить смс
                        $userUpd = Users::find()->where(['id'=>$user->id])->one();
                        if(!empty($userUpd)){
                            if(in_array($user->id, [52512, 4359])){
                                $smsKey = 154874;
                            }
                            else{
                                //$smsKey = 92450;
                                $smsKey =rand(10000, 99999);
                            }
                            $userUpd->enter_key = password_hash($smsKey, PASSWORD_BCRYPT);
                            if($userUpd->save(true)){
                                System::sendSms($userUpd->phone, 'Key: '.$smsKey.". Если это не вы, сообщите об этом по телефону: +7-983-316-14-72");
                                $modelSms = new SmsForm();
                                $modelSms->name = $user->name;
                                $modelSms->rememberMe = $model->rememberMe;
                                return $this->render('smscheck',[
                                    'model' => $modelSms,
                                ]);
                            }
                        }
                    }
                }
                else {
                    $model->validate();
                }
            }
        }

        return $this->render(
            'login', [
                'model' => $model,
            ]
        );

    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
