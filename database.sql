create table applicant_data
(
    id                           int auto_increment
        primary key,
    get_first_unused_json        json                                  null comment 'Respuesta JSON del paso get_first_unused',
    get_first_unused_status      tinyint     default 0                 null comment '0 = Pendiente, 1 = Completado, 2 = Error',
    get_first_unused_error       text                                  null comment 'Mensaje de error si ocurrió alguno',
    create_request_pk            int                                   null comment 'PK obtenido de la respuesta create_request',
    create_request_status        tinyint     default 0                 null comment '0 = Pendiente, 1 = Completado, 2 = Error',
    create_request_error         text                                  null comment 'Mensaje de error si ocurrió alguno',
    pl_upload_document_json      json                                  null comment 'Respuesta JSON del paso pl_upload_document (las 3 respuestas)',
    pl_upload_document_status    tinyint     default 0                 null comment '0 = Pendiente, 1 = Completado, 2 = Error',
    pl_upload_document_error     text                                  null comment 'Mensaje de error si ocurrió alguno',
    generates_tbs_receipt_json   json                                  null comment 'Respuesta JSON del paso generates_tbs_receipt',
    generates_tbs_receipt_status tinyint     default 0                 null comment '0 = Pendiente, 1 = Completado, 2 = Error',
    generates_tbs_receipt_error  text                                  null comment 'Mensaje de error si ocurrió alguno',
    pl_get_document_json         longtext                              null comment 'Respuesta JSON del paso pl_get_document',
    pl_get_document_status       tinyint     default 0                 null comment '0 = Pendiente, 1 = Completado, 2 = Error, 3 = Omitido',
    pl_get_document_error        text                                  null comment 'Mensaje de error si ocurrió alguno',
    pl_approve_json              json                                  null comment 'Respuesta JSON del paso pl_approve',
    pl_approve_status            tinyint     default 0                 null comment '0 = Pendiente, 1 = Completado, 2 = Error',
    pl_approve_error             text                                  null comment 'Mensaje de error si ocurrió alguno',
    overall_status               varchar(20) default 'PENDIENTE'       null comment 'PENDIENTE, PROCESANDO, COMPLETADO, ERROR',
    created_at                   timestamp   default CURRENT_TIMESTAMP null,
    updated_at                   timestamp   default CURRENT_TIMESTAMP null on update CURRENT_TIMESTAMP
);

create table error_operation
(
    id         bigint auto_increment
        primary key,
    operation  varchar(255)                        null,
    message    longtext                            null,
    created_at timestamp default CURRENT_TIMESTAMP null
);

create table ra_data
(
    id         int auto_increment
        primary key,
    ra_id      varchar(20)                         not null comment 'ID de la Autoridad de Registro',
    type       varchar(50)                         null comment 'Tipo de RA',
    status     tinyint   default 1                 null comment '1 = Activo, 0 = Inactivo',
    created_at timestamp default CURRENT_TIMESTAMP null,
    updated_at timestamp default CURRENT_TIMESTAMP null on update CURRENT_TIMESTAMP
);

create table rao_data
(
    id         int auto_increment
        primary key,
    rao_id     varchar(20)                           not null comment 'ID del Operador Autorizado',
    username   varchar(100)                          null comment 'Nombre de usuario para autenticación',
    password   varchar(100)                          null comment 'Contraseña para autenticación',
    pin        varchar(100)                          null comment 'PIN del operador',
    lang       varchar(10) default 'ES'              null comment 'Idioma (ES, EN, etc.)',
    type       varchar(50)                           null comment 'Tipo de RAO',
    status     tinyint     default 1                 null comment '1 = Activo, 0 = Inactivo',
    created_at timestamp   default CURRENT_TIMESTAMP null,
    updated_at timestamp   default CURRENT_TIMESTAMP null on update CURRENT_TIMESTAMP
);

