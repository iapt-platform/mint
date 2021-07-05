package mint

import (
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
)

func OpenDb() (*gorm.DB, error) {
	dsn := "host=127.0.0.1 user=postgres password=gorm dbname=mint port=5432 sslmode=disable TimeZone=Asia/Shanghai"
	return gorm.Open(postgres.Open(dsn), &gorm.Config{})
}
