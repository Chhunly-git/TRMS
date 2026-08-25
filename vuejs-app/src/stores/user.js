import { defineStore } from 'pinia';

export const useUserStore = defineStore('user',
  {
    state: () => ({
      id: null,
      name: null,
      name_kh: null,
      name_en: null,
      email: null,
      profile_image: null,
      profile_thumbnail: null,
      password_null: true,
      level: null,
      status: null,
      employee_type: null,
      employee_code: null,
      mef_card_number: null,
      department_id: null,
      office_id: null,
      position_id: null,
      department: null,
      office: null,
      position: null,

      // ព័ត៌មានផ្ទាល់ខ្លួនបន្ថែម
      gender: null,
      dob: null,
      marital_status: null,
      birth_place: null,
      current_address: null,
      phone: null,
      national_id_number: null,
      national_id_expired_date: null,
      passport_number: null,
      passport_expired_date: null,

      // កាលបរិច្ឆេទ និងប្រវត្តិការងារដំបូង
      first_service_date: null,
      first_appointment_date: null,
      initial_framework: null,
      initial_position: null,
      initial_ministry: null,
      initial_unit: null,
      initial_department: null,
      initial_office: null,

      // ស្ថានភាពមុខងារបច្ចុប្បន្ន
      current_framework: null,
      current_appointment_date: null,
      current_position_date: null,
    }),
    getters: {
      isAuthenticated: (state) => !!state.id,
      // ពិនិត្យសិទ្ធិ Admin យ៉ាងមានសុវត្ថិភាព (មិនខ្វល់រឿងអក្សរតូច/ធំ)
      isAdmin: (state) => {
        if (!state.level) return false;
        return String(state.level).trim().toUpperCase() === 'ADMIN';
      },
    },
    actions: {
      // User state management
      setState(user) {
        this.id = user.id;
        this.name = user.name;
        this.name_kh = user.name_kh;
        this.name_en = user.name_en;
        this.email = user.email;
        this.profile_image = user.profile_image;
        this.profile_thumbnail = user.profile_thumbnail;
        this.password_null = user.password_null;
        
        // 🟢 កន្លែងដែលត្រូវ Assign level និង status
        this.level = user.level;
        this.status = user.status;
        if (user.level) {
          localStorage.setItem("user_level", user.level);
        }

        this.employee_type = user.employee_type;
        this.employee_code = user.employee_code;
        this.mef_card_number = user.mef_card_number;

        // ព័ត៌មានផ្ទាល់ខ្លួន
        this.gender = user.gender;
        this.dob = user.dob;
        this.marital_status = user.marital_status;
        this.birth_place = user.birth_place;
        this.current_address = user.current_address;
        this.phone = user.phone;
        this.national_id_number = user.national_id_number;
        this.national_id_expired_date = user.national_id_expired_date;
        this.passport_number = user.passport_number;
        this.passport_expired_date = user.passport_expired_date;

        // ស្ថានភាពការងារដំបូង
        this.first_service_date = user.first_service_date;
        this.first_appointment_date = user.first_appointment_date;
        this.initial_framework = user.initial_framework;
        this.initial_position = user.initial_position;
        this.initial_ministry = user.initial_ministry;
        this.initial_unit = user.initial_unit;
        this.initial_department = user.initial_department;
        this.initial_office = user.initial_office;

        // ស្ថានភាពការងារបច្ចុប្បន្ន
        this.current_framework = user.current_framework;
        this.current_appointment_date = user.current_appointment_date;
        this.current_position_date = user.current_position_date;

        // Relationships
        this.department = user.department;
        this.office = user.office;
        this.position = user.position;
      },

      resetState() {
        this.id = null;
        this.name = null;
        this.name_kh = null;
        this.name_en = null;
        this.email = null;
        this.profile_image = null;
        this.profile_thumbnail = null;
        this.password_null = true;
        this.level = null;
        this.status = null;
        this.employee_type = null;
        this.employee_code = null;
        this.mef_card_number = null;
        this.department_id = null;
        this.office_id = null;
        this.position_id = null;
        this.department = null;
        this.office = null;
        this.position = null;

        // Reset ព័ត៌មានផ្ទាល់ខ្លួនផ្សេងៗ
        this.gender = null;
        this.dob = null;
        this.marital_status = null;
        this.birth_place = null;
        this.current_address = null;
        this.phone = null;
        this.national_id_number = null;
        this.national_id_expired_date = null;
        this.passport_number = null;
        this.passport_expired_date = null;

        // Reset ពេលចាកចេញ
        this.first_service_date = null;
        this.first_appointment_date = null;
        this.initial_framework = null;
        this.initial_position = null;
        this.initial_ministry = null;
        this.initial_unit = null;
        this.initial_department = null;
        this.initial_office = null;

        this.current_framework = null;
        this.current_appointment_date = null;
        this.current_position_date = null;

        localStorage.removeItem("user_level");
      },

      // User Sanctum Token management
      setSanctumToken(token) {
        localStorage.setItem('SANCTUM-TOKEN', token);
      },
      getSanctumToken() {
        return localStorage.getItem('SANCTUM-TOKEN');
      },
      removeSanctumToken() {
        localStorage.removeItem('SANCTUM-TOKEN');
      },

      // Reset user state and remove Sanctum token (e.g., on sign out)
      reset() {
        this.resetState();
        this.removeSanctumToken();
      },
    },
    persist: true,
  }
);