<?php

namespace wdmg\api\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use wdmg\api\models\API;

/**
 * APISearch represents the model behind the search form of `wdmg\api\models\API`.
 */
class APISearch extends API
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'status'], 'integer'],
            [['user_ip', 'access_token'], 'string'],
            [['created_at', 'updated_at', 'allowance_at', 'expired_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = API::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_ip' => $this->user_ip,
            'access_token' => $this->access_token,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'allowance_at' => $this->allowance_at,
            'expired_at' => $this->expired_at,
        ]);

        return $dataProvider;
    }
}
