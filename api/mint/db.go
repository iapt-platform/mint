package mint

import (
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
)

func OpenDb() (*gorm.DB, error) {
	dsn := "host=127.0.0.1 user=postgres password=gorm dbname=mint port=5432 sslmode=disable TimeZone=Asia/Shanghai"
	db, err := gorm.Open(postgres.Open(dsn), &gorm.Config{})
	if err != nil {
		return nil, err
	}

	db.Exec("CREATE TABLE IF NOT EXISTS t1(ID  SERIAL PRIMARY KEY, NAME VARCHAR(32) NOT NULL)")
	return db, nil
}
