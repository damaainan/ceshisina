# [javer的表结构设计][0]

## 序

javers设计了几张表，这里简单介绍下

## jv_snapshot

> 主要用来存储对象的每次变更操作以及变更的属性值。

```sql
    -- ----------------------------
    --  Table structure for jv_snapshot
    -- ----------------------------
    DROP TABLE IF EXISTS "public"."jv_snapshot";
    CREATE TABLE "public"."jv_snapshot" (
        "snapshot_pk" int8 NOT NULL,
        "type" varchar(200) COLLATE "default",
        "version" int8,
        "state" text COLLATE "default",
        "changed_properties" text COLLATE "default",
        "managed_type" varchar(200) COLLATE "default",
        "global_id_fk" int8,
        "commit_fk" int8
    )
    WITH (OIDS=FALSE);
    ALTER TABLE "public"."jv_snapshot" OWNER TO "postgres";
    
    -- ----------------------------
    --  Primary key structure for table jv_snapshot
    -- ----------------------------
    ALTER TABLE "public"."jv_snapshot" ADD PRIMARY KEY ("snapshot_pk") NOT DEFERRABLE INITIALLY IMMEDIATE;
    
    -- ----------------------------
    --  Indexes structure for table jv_snapshot
    -- ----------------------------
    CREATE INDEX  "jv_snapshot_commit_fk_idx" ON "public"."jv_snapshot" USING btree(commit_fk "pg_catalog"."int8_ops" ASC NULLS LAST);
    CREATE INDEX  "jv_snapshot_global_id_fk_idx" ON "public"."jv_snapshot" USING btree(global_id_fk "pg_catalog"."int8_ops" ASC NULLS LAST);
    
    -- ----------------------------
    --  Foreign keys structure for table jv_snapshot
    -- ----------------------------
    ALTER TABLE "public"."jv_snapshot" ADD CONSTRAINT "jv_snapshot_commit_fk" FOREIGN KEY ("commit_fk") REFERENCES "public"."jv_commit" ("commit_pk") ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
    ALTER TABLE "public"."jv_snapshot" ADD CONSTRAINT "jv_snapshot_global_id_fk" FOREIGN KEY ("global_id_fk") REFERENCES "public"."jv_global_id" ("global_id_pk") ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
```

## jv_global_id

> 存储了每次变更的id

```sql
    -- ----------------------------
    --  Table structure for jv_global_id
    -- ----------------------------
    DROP TABLE IF EXISTS "public"."jv_global_id";
    CREATE TABLE "public"."jv_global_id" (
        "global_id_pk" int8 NOT NULL,
        "local_id" varchar(200) COLLATE "default",
        "fragment" varchar(200) COLLATE "default",
        "type_name" varchar(200) COLLATE "default",
        "owner_id_fk" int8
    )
    WITH (OIDS=FALSE);
    ALTER TABLE "public"."jv_global_id" OWNER TO "postgres";
    
    -- ----------------------------
    --  Primary key structure for table jv_global_id
    -- ----------------------------
    ALTER TABLE "public"."jv_global_id" ADD PRIMARY KEY ("global_id_pk") NOT DEFERRABLE INITIALLY IMMEDIATE;
    
    -- ----------------------------
    --  Indexes structure for table jv_global_id
    -- ----------------------------
    CREATE INDEX  "jv_global_id_local_id_idx" ON "public"."jv_global_id" USING btree(local_id COLLATE "default" "pg_catalog"."text_ops" ASC NULLS LAST);
    
    -- ----------------------------
    --  Foreign keys structure for table jv_global_id
    -- ----------------------------
    ALTER TABLE "public"."jv_global_id" ADD CONSTRAINT "jv_global_id_owner_id_fk" FOREIGN KEY ("owner_id_fk") REFERENCES "public"."jv_global_id" ("global_id_pk") ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
```

## jv_commit

> 存储了每次变更的时间、变更人

```sql
    -- ----------------------------
    --  Table structure for jv_commit
    -- ----------------------------
    DROP TABLE IF EXISTS "public"."jv_commit";
    CREATE TABLE "public"."jv_commit" (
        "commit_pk" int8 NOT NULL,
        "author" varchar(200) COLLATE "default",
        "commit_date" timestamp(6) NULL,
        "commit_id" numeric(22,2)
    )
    WITH (OIDS=FALSE);
    ALTER TABLE "public"."jv_commit" OWNER TO "postgres";
    
    -- ----------------------------
    --  Primary key structure for table jv_commit
    -- ----------------------------
    ALTER TABLE "public"."jv_commit" ADD PRIMARY KEY ("commit_pk") NOT DEFERRABLE INITIALLY IMMEDIATE;
    
    -- ----------------------------
    --  Indexes structure for table jv_commit
    -- ----------------------------
    CREATE INDEX  "jv_commit_commit_id_idx" ON "public"."jv_commit" USING btree(commit_id "pg_catalog"."numeric_ops" ASC NULLS LAST);
```

支持自动从SpringSecurity获取auditor

    public class SpringSecurityAuthorProvider implements AuthorProvider {
        public SpringSecurityAuthorProvider() {
        }
    
        public String provide() {
            Authentication auth = SecurityContextHolder.getContext().getAuthentication();
            return auth == null?"unauthenticated":auth.getName();
        }
    }

也可以自定义，比如

    @Bean
        public AuthorProvider authorProvider() {
            return new MockAuthorProvider();
        }

## jv_commit_property

> 用来存储额外的信息，比如存储了用户id，顺带存一下用户名等。

```sql
    -- ----------------------------
    --  Table structure for jv_commit_property
    -- ----------------------------
    DROP TABLE IF EXISTS "public"."jv_commit_property";
    CREATE TABLE "public"."jv_commit_property" (
        "property_name" varchar(200) NOT NULL COLLATE "default",
        "property_value" varchar(600) COLLATE "default",
        "commit_fk" int8 NOT NULL
    )
    WITH (OIDS=FALSE);
    ALTER TABLE "public"."jv_commit_property" OWNER TO "postgres";
    
    -- ----------------------------
    --  Primary key structure for table jv_commit_property
    -- ----------------------------
    ALTER TABLE "public"."jv_commit_property" ADD PRIMARY KEY ("commit_fk", "property_name") NOT DEFERRABLE INITIALLY IMMEDIATE;
    
    -- ----------------------------
    --  Indexes structure for table jv_commit_property
    -- ----------------------------
    CREATE INDEX  "jv_commit_property_commit_fk_idx" ON "public"."jv_commit_property" USING btree(commit_fk "pg_catalog"."int8_ops" ASC NULLS LAST);
    CREATE INDEX  "jv_commit_property_property_name_property_value_idx" ON "public"."jv_commit_property" USING btree(property_name COLLATE "default" "pg_catalog"."text_ops" ASC NULLS LAST, property_value COLLATE "default" "pg_catalog"."text_ops" ASC NULLS LAST);
    
    -- ----------------------------
    --  Foreign keys structure for table jv_commit_property
    -- ----------------------------
    ALTER TABLE "public"."jv_commit_property" ADD CONSTRAINT "jv_commit_property_commit_fk" FOREIGN KEY ("commit_fk") REFERENCES "public"."jv_commit" ("commit_pk") ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
```
可以这样自定义

    @Bean
        public CommitPropertiesProvider commitPropertiesProvider() {
            return new CommitPropertiesProvider() {
                @Override
                public Map<String, String> provide() {
                    return ImmutableMap.of("key", "ok");
                }
            };
        }

## doc

* [javers][11]

[0]: /a/1190000010402526
[1]: /t/java/blogs
[2]: /u/xixicat
[11]: http://javers.org/documentation/